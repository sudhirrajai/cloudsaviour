<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use App\Models\RdsInstance;

class RdsService
{
    public function __construct(
        private AwsClientFactory $factory
    ) {}

    public function syncInstances(Workspace $workspace): int
    {
        $client = $this->factory->rds($workspace);

        $result = $client->describeDBInstances();

        $synced = 0;
        $dbIds = [];

        foreach ($result['DBInstances'] as $db) {
            $dbIds[] = $db['DBInstanceIdentifier'];

            RdsInstance::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'db_instance_id' => $db['DBInstanceIdentifier'],
                ],
                [
                    'db_name' => $db['DBName'] ?? null,
                    'db_engine' => $db['Engine'] . ' ' . ($db['EngineVersion'] ?? ''),
                    'instance_class' => $db['DBInstanceClass'],
                    'status' => $db['DBInstanceStatus'],
                    'endpoint' => $db['Endpoint']['Address'] ?? null,
                    'port' => $db['Endpoint']['Port'] ?? 3306,
                    'last_synced_at' => now(),
                ]
            );
            $synced++;
        }

        // Remove stale entries
        if (!empty($dbIds)) {
            RdsInstance::where('workspace_id', $workspace->id)
                ->whereNotIn('db_instance_id', $dbIds)
                ->delete();
        }

        return $synced;
    }

    public function startInstance(Workspace $workspace, string $dbInstanceId): void
    {
        $client = $this->factory->rds($workspace);
        $client->startDBInstance(['DBInstanceIdentifier' => $dbInstanceId]);

        RdsInstance::where('workspace_id', $workspace->id)
            ->where('db_instance_id', $dbInstanceId)
            ->update(['status' => 'starting']);
    }

    public function stopInstance(Workspace $workspace, string $dbInstanceId): void
    {
        $client = $this->factory->rds($workspace);
        $client->stopDBInstance(['DBInstanceIdentifier' => $dbInstanceId]);

        RdsInstance::where('workspace_id', $workspace->id)
            ->where('db_instance_id', $dbInstanceId)
            ->update(['status' => 'stopping']);
    }

    public function syncSingleInstance(Workspace $workspace, string $dbInstanceId): string
    {
        $client = $this->factory->rds($workspace);

        $result = $client->describeDBInstances([
            'DBInstanceIdentifier' => $dbInstanceId,
        ]);

        if (empty($result['DBInstances'])) {
            return 'not-found';
        }

        $db = $result['DBInstances'][0];

        RdsInstance::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'db_instance_id' => $db['DBInstanceIdentifier'],
            ],
            [
                'db_name' => $db['DBName'] ?? null,
                'db_engine' => $db['Engine'] . ' ' . ($db['EngineVersion'] ?? ''),
                'instance_class' => $db['DBInstanceClass'],
                'status' => $db['DBInstanceStatus'],
                'endpoint' => $db['Endpoint']['Address'] ?? null,
                'port' => $db['Endpoint']['Port'] ?? 3306,
                'last_synced_at' => now(),
            ]
        );

        return $db['DBInstanceStatus'];
    }
}
