<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use Aws\Ec2\Ec2Client;
use Aws\Rds\RdsClient;
use Aws\CostExplorer\CostExplorerClient;
use Aws\CloudWatch\CloudWatchClient;

class AwsClientFactory
{
    public function ec2(Workspace $workspace): Ec2Client
    {
        return new Ec2Client($this->baseConfig($workspace));
    }

    public function rds(Workspace $workspace): RdsClient
    {
        return new RdsClient($this->baseConfig($workspace));
    }

    public function costExplorer(Workspace $workspace): CostExplorerClient
    {
        // Cost Explorer always uses us-east-1
        $config = $this->baseConfig($workspace);
        $config['region'] = 'us-east-1';
        return new CostExplorerClient($config);
    }

    public function cloudWatch(Workspace $workspace): CloudWatchClient
    {
        return new CloudWatchClient($this->baseConfig($workspace));
    }

    private function baseConfig(Workspace $workspace): array
    {
        return [
            'version' => 'latest',
            'region'  => $workspace->aws_region,
            'credentials' => [
                'key'    => $workspace->aws_access_key,
                'secret' => $workspace->aws_secret_key,
            ],
        ];
    }
}
