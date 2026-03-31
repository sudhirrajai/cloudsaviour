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

    public function handle(IdleScannerService $scanner): void
    {
        $query = Workspace::where('is_active', true)
            ->whereNotNull('aws_access_key')
            ->whereNotNull('aws_secret_key');

        if ($this->workspaceId) {
            $query->where('id', $this->workspaceId);
        }

        $query->each(function (Workspace $workspace) use ($scanner) {
            try {
                $scanner->scan($workspace);
            } catch (\Exception $e) {
                report($e);
            }
        });
    }
}
