<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\Schedule;
use App\Services\Aws\Ec2Service;
use App\Services\Aws\RdsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunScheduledActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(Ec2Service $ec2Service, RdsService $rdsService): void
    {
        $now = Carbon::now();

        Schedule::where('is_active', true)
            ->with('workspace')
            ->each(function (Schedule $schedule) use ($now, $ec2Service, $rdsService) {
                try {
                    // Check if current day is in schedule
                    $localNow = $now->copy()->setTimezone($schedule->timezone);
                    $currentDow = $localNow->dayOfWeekIso; // 1=Mon, 7=Sun
                    $currentTime = $localNow->format('H:i');
                    
                    Log::info("Checking Schedule: {$schedule->name} (Target: {$schedule->time_of_day}, Current: {$currentTime}, Timezone: {$schedule->timezone})");

                    if (!in_array($currentDow, $schedule->days_of_week)) {
                        Log::info("Skipping Schedule: {$schedule->name} - Day Mismatch (Now: {$currentDow}, Allowed: " . json_encode($schedule->days_of_week) . ")");
                        return;
                    }

                    // Check if current time matches (or is within a 15-minute buffer for manual runs)
                    $targetTime = \Carbon\Carbon::createFromFormat('H:i', $schedule->time_of_day, $schedule->timezone);
                    $diffInMinutes = $localNow->diffInMinutes($targetTime, false); // negative if target is in past

                    // We allow execution if: 
                    // 1. It's the exact minute
                    // 2. OR it's within 60 minutes AFTER the target time (to catch manual runs/cron delays)
                    // 3. AND it hasn't run yet today
                    $isWithinBuffer = ($diffInMinutes <= 0 && $diffInMinutes >= -60);
                    $alreadyRunToday = $schedule->last_run_at && $schedule->last_run_at->setTimezone($schedule->timezone)->isSameDay($localNow);

                    if (!$isWithinBuffer) {
                        Log::info("Skipping Schedule: {$schedule->name} - Time Mismatch (Diff: {$diffInMinutes} mins)");
                        return;
                    }

                    if ($alreadyRunToday) {
                        Log::info("Skipping Schedule: {$schedule->name} - Already run today at " . $schedule->last_run_at->toDateTimeString());
                        return;
                    }

                    Log::info("Executing Schedule: {$schedule->name} for action: {$schedule->action}");

                    // Execute action
                    $workspace = $schedule->workspace;
                    if (!$workspace || !$workspace->hasAwsCredentials()) {
                        return;
                    }

                    if ($schedule->resource_type === 'ec2') {
                        if ($schedule->action === 'start') {
                            $ec2Service->startInstance($workspace, $schedule->resource_id);
                        } elseif ($schedule->action === 'stop') {
                            $ec2Service->stopInstance($workspace, $schedule->resource_id);
                        } elseif ($schedule->action === 'terminate') {
                            $ec2Service->terminateInstance($workspace, $schedule->resource_id);
                        }
                    } elseif ($schedule->resource_type === 'rds') {
                        if ($schedule->action === 'start') {
                            $rdsService->startInstance($workspace, $schedule->resource_id);
                        } elseif ($schedule->action === 'stop') {
                            $rdsService->stopInstance($workspace, $schedule->resource_id);
                        } elseif ($schedule->action === 'delete') {
                            $rdsService->deleteInstance($workspace, $schedule->resource_id);
                        }
                    }

                    // Log activity
                    $actionLabel = [
                        'start' => 'started',
                        'stop' => 'stopped',
                        'terminate' => 'terminated',
                        'delete' => 'deleted'
                    ][$schedule->action];

                    ActivityLog::create([
                        'workspace_id' => $workspace->id,
                        'actor_type' => 'schedule',
                        'action' => $actionLabel,
                        'resource_type' => strtoupper($schedule->resource_type),
                        'resource_id' => $schedule->resource_id,
                        'resource_name' => $schedule->name,
                    ]);

                    // Update schedule
                    $schedule->update(['last_run_at' => $now]);
                } catch (\Exception $e) {
                    report($e);
                    
                    if ($schedule->workspace->shouldNotify('scheduled action failures')) {
                        \Illuminate\Support\Facades\Mail::to($schedule->workspace->owner->email)->send(
                            new \App\Mail\ScheduledActionFailureMail(
                                $schedule->workspace->name, 
                                $schedule->name, 
                                $e->getMessage()
                            )
                        );
                    }
                }
            });
    }
}
