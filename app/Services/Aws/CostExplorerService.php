<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CostExplorerService
{
    private $clientFactory;

    public function __construct(AwsClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * Get the estimated cost for the current month so far.
     */
    public function getMonthlyCost(Workspace $workspace): float
    {
        if (!$workspace->hasAwsCredentials()) {
            return 0.00;
        }

        $client = $this->clientFactory->costExplorer($workspace);
        
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->addDay()->format('Y-m-d'); // End date is exclusive

        try {
            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => $start,
                    'End' => $end,
                ],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['UnblendedCost', 'UsageQuantity'],
            ]);

            return (float) ($result['ResultsByTime'][0]['Total']['UnblendedCost']['Amount'] ?? 0.00);
        } catch (\Exception $e) {
            Log::error("AWS Cost Explorer Error (Monthly): " . $e->getMessage());
            return 0.00;
        }
    }

    /**
     * Get daily cost history for the last 30 days.
     */
    public function getDailyCostHistory(Workspace $workspace): array
    {
        if (!$workspace->hasAwsCredentials()) {
            return [];
        }

        $client = $this->clientFactory->costExplorer($workspace);
        
        $start = Carbon::now()->subDays(30)->format('Y-m-d');
        $end = Carbon::now()->addDay()->format('Y-m-d');

        try {
            $result = $client->getCostAndUsage([
                'TimePeriod' => [
                    'Start' => $start,
                    'End' => $end,
                ],
                'Granularity' => 'DAILY',
                'Metrics' => ['UnblendedCost', 'UsageQuantity'],
                'GroupBy' => [
                    ['Type' => 'DIMENSION', 'Key' => 'SERVICE']
                ],
            ]);

            $history = [];
            foreach ($result['ResultsByTime'] as $day) {
                $date = $day['TimePeriod']['Start'];
                $total = 0;
                $services = [];

                foreach ($day['Groups'] as $group) {
                    $serviceName = $group['Keys'][0];
                    $amount = (float) $group['Metrics']['UnblendedCost']['Amount'];
                    $services[$serviceName] = $amount;
                    $total += $amount;
                }

                $history[] = [
                    'date' => $date,
                    'total' => $total,
                    'services' => $services,
                ];
            }

            return $history;
        } catch (\Exception $e) {
            Log::error("AWS Cost Explorer Error (History): " . $e->getMessage());
            return [];
        }
    }
}
