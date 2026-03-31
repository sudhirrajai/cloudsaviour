<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use App\Models\IdleResource;

class IdleScannerService
{
    public function __construct(
        private AwsClientFactory $factory
    ) {}

    public function scan(Workspace $workspace): int
    {
        $detected = 0;
        $detected += $this->scanUnattachedVolumes($workspace);
        $detected += $this->scanUnassociatedElasticIps($workspace);
        $detected += $this->scanIdleNatGateways($workspace);
        return $detected;
    }

    public function deleteResource(Workspace $workspace, IdleResource $resource): bool
    {
        $client = $this->factory->ec2($workspace);

        switch ($resource->resource_type) {
            case 'ebs_volume':
                $client->deleteVolume(['VolumeId' => $resource->resource_id]);
                break;
            case 'elastic_ip':
                $client->releaseAddress(['AllocationId' => $resource->resource_id]);
                break;
            case 'nat_gateway':
                $client->deleteNatGateway(['NatGatewayId' => $resource->resource_id]);
                break;
            default:
                throw new \Exception("Unsupported resource type for deletion: {$resource->resource_type}");
        }

        return true;
    }

    private function scanUnattachedVolumes(Workspace $workspace): int
    {
        $client = $this->factory->ec2($workspace);
        $count = 0;

        $result = $client->describeVolumes([
            'Filters' => [
                ['Name' => 'status', 'Values' => ['available']], // available = not attached
            ],
        ]);

        foreach ($result['Volumes'] as $vol) {
            $name = '';
            foreach ($vol['Tags'] ?? [] as $tag) {
                if ($tag['Key'] === 'Name') {
                    $name = $tag['Value'];
                    break;
                }
            }

            // Estimate cost: ~$0.10/GB/month for gp2/gp3
            $costPerMonth = ($vol['Size'] ?? 0) * 0.10;

            IdleResource::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'resource_type' => 'ebs_volume',
                    'resource_id' => $vol['VolumeId'],
                ],
                [
                    'resource_name' => $name ?: $vol['VolumeId'],
                    'details' => [
                        'size_gb' => $vol['Size'],
                        'volume_type' => $vol['VolumeType'],
                        'created' => (string) ($vol['CreateTime'] ?? ''),
                    ],
                    'estimated_monthly_cost' => $costPerMonth,
                    'detected_at' => now(),
                    'resolved_at' => null, // If it's detected again, it's not resolved!
                ]
            );
            $count++;
        }

        return $count;
    }

    private function scanUnassociatedElasticIps(Workspace $workspace): int
    {
        $client = $this->factory->ec2($workspace);
        $count = 0;

        $result = $client->describeAddresses();

        foreach ($result['Addresses'] as $eip) {
            // Unassociated if no InstanceId and no AssociationId
            if (empty($eip['InstanceId']) && empty($eip['AssociationId'])) {
                IdleResource::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'resource_type' => 'elastic_ip',
                        'resource_id' => $eip['AllocationId'],
                    ],
                    [
                        'resource_name' => $eip['PublicIp'] ?? $eip['AllocationId'],
                        'details' => [
                            'public_ip' => $eip['PublicIp'] ?? null,
                            'domain' => $eip['Domain'] ?? 'vpc',
                        ],
                        'estimated_monthly_cost' => 3.60, // ~$0.005/hr when unassociated
                        'detected_at' => now(),
                        'resolved_at' => null,
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    private function scanIdleNatGateways(Workspace $workspace): int
    {
        $client = $this->factory->ec2($workspace);
        $count = 0;

        $result = $client->describeNatGateways([
            'Filter' => [
                ['Name' => 'state', 'Values' => ['available']],
            ],
        ]);

        foreach ($result['NatGateways'] as $nat) {
            $name = '';
            foreach ($nat['Tags'] ?? [] as $tag) {
                if ($tag['Key'] === 'Name') {
                    $name = $tag['Value'];
                    break;
                }
            }

            // NAT Gateway costs ~$0.045/hr = ~$32.40/month
            IdleResource::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'resource_type' => 'nat_gateway',
                    'resource_id' => $nat['NatGatewayId'],
                ],
                [
                    'resource_name' => $name ?: $nat['NatGatewayId'],
                    'details' => [
                        'subnet_id' => $nat['SubnetId'] ?? null,
                        'vpc_id' => $nat['VpcId'] ?? null,
                        'created' => (string) ($nat['CreateTime'] ?? ''),
                    ],
                    'estimated_monthly_cost' => 32.40,
                    'detected_at' => now(),
                    'resolved_at' => null,
                ]
            );
            $count++;
        }

        return $count;
    }
}
