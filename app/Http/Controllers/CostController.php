<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class CostController extends Controller
{
    public function index(Request $request, \App\Services\Aws\CostExplorerService $costService)
    {
        $workspace = app('activeWorkspace');

        if (!$workspace->hasAwsCredentials()) {
            return Inertia::render('Dashboard/Cost/Index', [
                'costRecords' => [],
                'totalThisMonth' => 0.00,
                'services' => [],
                'history' => [],
            ]);
        }

        $monthlyTotal = $costService->getMonthlyCost($workspace);
        $dailyHistory = $costService->getDailyCostHistory($workspace);

        // Get last month's total and breakdown for comparison
        $lastMonthStart = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = now()->subMonth()->endOfMonth()->addDay()->format('Y-m-d');
        
        $lastMonthResult = [];
        try {
            $client = app(\App\Services\Aws\AwsClientFactory::class)->costExplorer($workspace);
            $lastMonthResult = $client->getCostAndUsage([
                'TimePeriod' => ['Start' => $lastMonthStart, 'End' => $lastMonthEnd],
                'Granularity' => 'MONTHLY',
                'Metrics' => ['UnblendedCost'],
                'GroupBy' => [['Type' => 'DIMENSION', 'Key' => 'SERVICE']]
            ]);
        } catch (\Exception $e) { /* ignore */ }

        $lastMonthBreakdown = [];
        foreach ($lastMonthResult['ResultsByTime'][0]['Groups'] ?? [] as $group) {
            $lastMonthBreakdown[$group['Keys'][0]] = (float) $group['Metrics']['UnblendedCost']['Amount'];
        }

        // Process service breakdown from daily history (cumulative for current month)
        $serviceTotals = [];
        foreach ($dailyHistory as $day) {
            if (Carbon::parse($day['date'])->isCurrentMonth()) {
                foreach ($day['services'] as $name => $amount) {
                    $serviceTotals[$name] = ($serviceTotals[$name] ?? 0) + $amount;
                }
            }
        }

        $services = [];
        foreach ($serviceTotals as $name => $amount) {
            $lastAmount = $lastMonthBreakdown[$name] ?? 0;
            $services[] = [
                'name' => $name,
                'thisMonth' => (float) $amount,
                'lastMonth' => (float) $lastAmount,
                'change' => (float) $amount - (float) $lastAmount,
                'pct' => $monthlyTotal > 0 ? round(($amount / $monthlyTotal) * 100, 1) : 0,
            ];
        }

        // Sort services by amount
        usort($services, fn($a, $b) => $b['thisMonth'] <=> $a['thisMonth']);

        return Inertia::render('Dashboard/Cost/Index', [
            'totalThisMonth' => round($monthlyTotal, 2),
            'services' => $services,
            'history' => $dailyHistory,
        ]);
    }
}
