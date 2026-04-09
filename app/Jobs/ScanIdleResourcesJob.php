<?php

namespace App\Jobs;

use App\Models\Workspace;
use App\Services\Aws\IdleScannerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScanIdleResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $workspaceId = null
    ) {}

    public function handle(IdleScannerService $scanner, \App\Services\Aws\AiEngineService $aiEngine): void
    {
        $query = Workspace::where('is_active', true)
            ->whereNotNull('aws_access_key')
            ->whereNotNull('aws_secret_key');

        if ($this->workspaceId) {
            $query->where('id', $this->workspaceId);
        }

        $query->each(function (Workspace $workspace) use ($scanner, $aiEngine) {
            try {
                $beforeCount = $workspace->idleResources()->whereNull('resolved_at')->count();
                $scanner->scan($workspace);
                $aiEngine->generateRecommendations($workspace);
                $afterCount = $workspace->idleResources()->whereNull('resolved_at')->count();

                if ($afterCount > $beforeCount && $workspace->shouldNotify('idle resource detection')) {
                    $newResources = $workspace->idleResources()
                        ->whereNull('resolved_at')
                        ->latest('detected_at')
                        ->take($afterCount - $beforeCount)
                        ->get()
                        ->map(fn($r) => [
                            'type' => $r->resource_type,
                            'id' => $r->resource_id,
                            'cost' => $r->estimated_monthly_cost
                        ])->toArray();

                    $totalSavings = array_sum(array_column($newResources, 'cost'));

                    \Illuminate\Support\Facades\Mail::to($workspace->owner->email)->send(
                        new \App\Mail\IdleResourceFoundMail($workspace->name, $newResources, $totalSavings)
                    );
                }
            } catch (\Exception $e) {
                report($e);
            }
        });
    }
}
