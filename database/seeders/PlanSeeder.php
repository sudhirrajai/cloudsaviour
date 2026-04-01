<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'For individual developers and small projects.',
                'price' => 0.00,
                'features' => ['1 Workspace', '2 EC2 Instances', '1 RDS Instance', 'Basic AI Insights'],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing teams and production workloads.',
                'price' => 49.00,
                'features' => ['5 Workspaces', 'Unlimited EC2', '5 RDS Instances', 'Priority Support', 'Advanced AI'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with complex needs.',
                'price' => 199.00,
                'features' => ['Unlimited everything', 'Dedicated Account Manager', 'Custom AI Models', 'SLA Guarantee'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            \App\Models\Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        // Sync existing workspaces
        foreach (\App\Models\Workspace::all() as $workspace) {
            $plan = \App\Models\Plan::where('slug', $workspace->plan)->first();
            if ($plan) {
                $workspace->update(['plan_id' => $plan->id]);
            } else {
                $freePlan = \App\Models\Plan::where('slug', 'free')->first();
                $workspace->update(['plan_id' => $freePlan->id]);
            }
        }
    }
}
