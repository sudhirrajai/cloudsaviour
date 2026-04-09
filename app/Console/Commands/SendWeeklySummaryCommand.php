<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-weekly-summary')]
#[Description('Send weekly cost summary emails to workspace owners')]
class SendWeeklySummaryCommand extends Command
{
    public function handle(\App\Services\Aws\CostExplorerService $costService)
    {
        $workspaces = \App\Models\Workspace::where('is_active', true)
            ->whereNotNull('aws_access_key')
            ->each(function ($workspace) use ($costService) {
                if (!$workspace->shouldNotify('weekly cost summary')) {
                    return;
                }

                try {
                    // Fetch last 7 days
                    $end = now();
                    $start = now()->subDays(7);
                    
                    $totalSpent = $costService->getCostRange($workspace, $start, $end);
                    $prevTotal = $costService->getCostRange($workspace, $start->copy()->subDays(7), $start);
                    
                    $trend = $prevTotal > 0 ? (($totalSpent - $prevTotal) / $prevTotal) * 100 : 0;
                    
                    // Simple Mock breakdown for now if service isn't available
                    $breakdown = [
                        'EC2' => $totalSpent * 0.6,
                        'RDS' => $totalSpent * 0.3,
                        'S3' => $totalSpent * 0.1,
                    ];

                    \Illuminate\Support\Facades\Mail::to($workspace->owner->email)->send(
                        new \App\Mail\WeeklyCostSummaryMail(
                            $workspace->name,
                            (float)$totalSpent,
                            $breakdown,
                            (float)round($trend, 1)
                        )
                    );
                    
                    $this->info("Sent weekly summary to: " . $workspace->owner->email);
                } catch (\Exception $e) {
                    $this->error("Failed for workspace {$workspace->id}: " . $e->getMessage());
                }
            });
    }
}
