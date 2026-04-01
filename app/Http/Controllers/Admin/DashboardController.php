<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Workspace;
use App\Models\CostRecord;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_workspaces' => Workspace::count(),
            'active_subscriptions' => Workspace::where('plan', '!=', 'free')->where('is_active', true)->count(),
            'total_platform_cost' => CostRecord::sum('amount'),
            'growth_rate' => $this->calculateGrowthRate(),
            'registrations_last_7_days' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $recent_users = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);
        
        $subscription_breakdown = Workspace::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recent_users,
            'subscriptionBreakdown' => $subscription_breakdown,
        ]);
    }

    private function calculateGrowthRate()
    {
        $thisMonth = User::whereMonth('created_at', now()->month)->count();
        $lastMonth = User::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($lastMonth === 0) return $thisMonth > 0 ? 100 : 0;
        
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }
}
