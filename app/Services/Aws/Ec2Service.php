<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use App\Models\Ec2Instance;

class Ec2Service
{
    public function __construct(
        private AwsClientFactory $factory
    ) {}

    /**
     * Sync EC2 instances from AWS to local database
     */
    public function syncInstances(Workspace $workspace): int
    {
        $client = $this->factory->ec2($workspace);

        $result = $client->describeInstances([
            'Filters' => [
                ['Name' => 'instance-state-name', 'Values' => ['running', 'stopped', 'pending', 'stopping', 'shutting-down', 'terminated']],
            ],
        ]);

        $synced = 0;
        $instanceIds = [];

        foreach ($result['Reservations'] as $reservation) {
            foreach ($reservation['Instances'] as $instance) {
                $name = '';
                foreach ($instance['Tags'] ?? [] as $tag) {
                    if ($tag['Key'] === 'Name') {
                        $name = $tag['Value'];
                        break;
                    }
                }

                $instanceIds[] = $instance['InstanceId'];

                Ec2Instance::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'instance_id' => $instance['InstanceId'],
                    ],
                    [
                        'name' => $name,
                        'instance_type' => $instance['InstanceType'],
                        'state' => $instance['State']['Name'],
                        'public_ip' => $instance['PublicIpAddress'] ?? null,
                        'private_ip' => $instance['PrivateIpAddress'] ?? null,
                        'region' => $workspace->aws_region,
                        'availability_zone' => $instance['Placement']['AvailabilityZone'],
                        'uptime_since' => $instance['State']['Name'] === 'running' ? ($instance['LaunchTime'] ?? null) : null,
                        'last_synced_at' => now(),
                    ]
                );
                $synced++;
            }
        }

        // Remove instances that no longer exist in AWS (or are not in our tracked states)
        Ec2Instance::where('workspace_id', $workspace->id)
            ->whereNotIn('instance_id', $instanceIds)
            ->delete();

        return $synced;
    }

    public function startInstance(Workspace $workspace, string $instanceId): void
    {
        $client = $this->factory->ec2($workspace);
        $client->startInstances(['InstanceIds' => [$instanceId]]);

        Ec2Instance::where('workspace_id', $workspace->id)
            ->where('instance_id', $instanceId)
            ->update(['state' => 'pending']);
    }

    public function stopInstance(Workspace $workspace, string $instanceId): void
    {
        $client = $this->factory->ec2($workspace);
        $client->stopInstances(['InstanceIds' => [$instanceId]]);

        Ec2Instance::where('workspace_id', $workspace->id)
            ->where('instance_id', $instanceId)
            ->update(['state' => 'stopping']);
    }

    public function terminateInstance(Workspace $workspace, string $instanceId): void
    {
        $client = $this->factory->ec2($workspace);
        $client->terminateInstances(['InstanceIds' => [$instanceId]]);

        Ec2Instance::where('workspace_id', $workspace->id)
            ->where('instance_id', $instanceId)
            ->update(['state' => 'shutting-down']);
    }

    public function deleteVolume(Workspace $workspace, string $volumeId): void
    {
        $client = $this->factory->ec2($workspace);
        $client->deleteVolume(['VolumeId' => $volumeId]);
    }

    public function releaseAddress(Workspace $workspace, string $allocationId): void
    {
        $client = $this->factory->ec2($workspace);
        $client->releaseAddress(['AllocationId' => $allocationId]);
    }

    public function syncSingleInstance(Workspace $workspace, string $instanceId): string
    {
        $client = $this->factory->ec2($workspace);

        $result = $client->describeInstances([
            'InstanceIds' => [$instanceId],
        ]);

        if (empty($result['Reservations'])) {
            return 'not-found';
        }

        $instance = $result['Reservations'][0]['Instances'][0];
        $name = '';
        foreach ($instance['Tags'] ?? [] as $tag) {
            if ($tag['Key'] === 'Name') {
                $name = $tag['Value'];
                break;
            }
        }

        Ec2Instance::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'instance_id' => $instance['InstanceId'],
            ],
            [
                'name' => $name,
                'instance_type' => $instance['InstanceType'],
                'state' => $instance['State']['Name'],
                'public_ip' => $instance['PublicIpAddress'] ?? null,
                'private_ip' => $instance['PrivateIpAddress'] ?? null,
                'region' => $workspace->aws_region,
                'availability_zone' => $instance['Placement']['AvailabilityZone'],
                'uptime_since' => $instance['State']['Name'] === 'running' ? ($instance['LaunchTime'] ?? null) : null,
                'last_synced_at' => now(),
            ]
        );

        return $instance['State']['Name'];
    }
}
