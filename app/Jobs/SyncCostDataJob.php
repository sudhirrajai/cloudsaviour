<?php

namespace App\Jobs;

use App\Models\Workspace;
use App\Services\Aws\CostExplorerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCostDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(CostExplorerService $costService): void
    {
        Workspace::where('is_active', true)
            ->whereNotNull('aws_access_key')
            ->whereNotNull('aws_secret_key')
            ->each(function (Workspace $workspace) use ($costService) {
                try {
                    $costService->syncMonthlyCosts($workspace);
                } catch (\Exception $e) {
                    report($e);
                }
            });
    }
}
