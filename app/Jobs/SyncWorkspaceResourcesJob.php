<?php

namespace App\Jobs;

use App\Models\Workspace;
use App\Services\Aws\Ec2Service;
use App\Services\Aws\RdsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWorkspaceResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $workspaceId = null
    ) {}

    public function handle(Ec2Service $ec2Service, RdsService $rdsService): void
    {
        $query = Workspace::where('is_active', true);

        if ($this->workspaceId) {
            $query->where('id', $this->workspaceId);
        }

        $query->whereNotNull('aws_access_key')
            ->whereNotNull('aws_secret_key')
            ->each(function (Workspace $workspace) use ($ec2Service, $rdsService) {
                try {
                    $ec2Service->syncInstances($workspace);
                    $rdsService->syncInstances($workspace);
                } catch (\Exception $e) {
                    report($e);
                }
            });
    }
}
