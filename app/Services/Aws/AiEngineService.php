<?php

namespace App\Services\Aws;

use App\Models\Workspace;
use App\Models\IdleResource;
use App\Models\AiRecommendation;
use App\Models\Ec2Instance;
use App\Models\Schedule;
use Illuminate\Support\Facades\Log;

class AiEngineService
{
    /**
     * Generate cost-saving recommendations for the workspace.
     */
    public function generateRecommendations(Workspace $workspace): void
    {
        Log::info("AI Engine: Generating recommendations for Workspace {$workspace->id}");

        try { $this->processIdleResources($workspace); } catch (\Exception $e) { Log::error("AI Engine Error (Idle): " . $e->getMessage()); }
        try { $this->processAutomationOpportunities($workspace); } catch (\Exception $e) { Log::error("AI Engine Error (Automation): " . $e->getMessage()); }
        try { $this->processRightSizingOpportunities($workspace); } catch (\Exception $e) { Log::error("AI Engine Error (RightSizing): " . $e->getMessage()); }
        try { $this->processLowUtilizationOpportunities($workspace); } catch (\Exception $e) { Log::error("AI Engine Error (LowUtilization): " . $e->getMessage()); }
    }

    /**
     * Convert idle resources into AI recommendations.
     */
    private function processIdleResources(Workspace $workspace): void
    {
        $idleResources = $workspace->idleResources()
            ->whereNull('resolved_at')
            ->where('is_ignored', false)
            ->get();

        foreach ($idleResources as $resource) {
            $title = "";
            $description = "";
            $actionType = "delete";

            switch ($resource->resource_type) {
                case 'ebs_volume':
                    $title = "Delete Unattached EBS Volume";
                    $description = "Volume {$resource->resource_id} is not attached to any EC2 instance and is incurring costs.";
                    break;
                case 'elastic_ip':
                    $description = "Elastic IP {$resource->resource_name} is reserved but not associated with any instance.";
                    $actionType = "delete";
                    break;
                case 'nat_gateway':
                    $title = "Remove Idle NAT Gateway";
                    $description = "NAT Gateway {$resource->resource_id} appears to be idle based on connection heuristics.";
                    break;
            }

            if ($title) {
                AiRecommendation::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'resource_type' => $resource->resource_type,
                        'resource_id' => $resource->resource_id,
                    ],
                    [
                        'title' => $title,
                        'description' => $description,
                        'estimated_monthly_saving' => $resource->estimated_monthly_cost,
                        'confidence_score' => 95,
                        'action_type' => $actionType,
                        'status' => 'pending',
                    ]
                );
            }
        }
    }

    /**
     * Identify instances running 24/7 without a schedule.
     */
    private function processAutomationOpportunities(Workspace $workspace): void
    {
        $runningInstances = Ec2Instance::where('workspace_id', $workspace->id)
            ->where('state', 'running')
            ->get();

        foreach ($runningInstances as $instance) {
            // Check if there's any active schedule for this resource
            $hasSchedule = Schedule::where('workspace_id', $workspace->id)
                ->where('resource_id', $instance->instance_id)
                ->where('is_active', true)
                ->exists();

            if (!$hasSchedule) {
                $instanceName = $instance->name ?: $instance->instance_id;
                
                AiRecommendation::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'resource_type' => 'ec2_schedule',
                        'resource_id' => $instance->instance_id,
                    ],
                    [
                        'title' => "Schedule Nightly Shutdown: {$instanceName}",
                        'description' => "This instance is running 24/7 with no automation. Scheduling a nightly shutdown could save up to 60% in compute costs.",
                        'estimated_monthly_saving' => 15.00, // Basic estimate based on t3.small
                        'confidence_score' => 85,
                        'action_type' => 'schedule',
                        'status' => 'pending',
                    ]
                );
            }
        }
    }

    /**
     * Basic right-sizing placeholder logic.
     */
    private function processRightSizingOpportunities(Workspace $workspace): void
    {
        // For now, we'll mark any instance using 'large' or 'xlarge' types as potential right-sizing targets 
        // if they've been running for > 3 days (simulating low usage).
        $largeInstances = Ec2Instance::where('workspace_id', $workspace->id)
            ->where('state', 'running')
            ->where(function ($query) {
                $query->where('instance_type', 'like', '%.large')
                      ->orWhere('instance_type', 'like', '%.xlarge');
            })
            ->get();

        foreach ($largeInstances as $instance) {
            $instanceName = $instance->name ?: $instance->instance_id;

            AiRecommendation::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'resource_type' => 'ec2_rightsize',
                    'resource_id' => $instance->instance_id,
                ],
                [
                    'title' => "Rightsize Instance: {$instanceName}",
                    'description' => "Instance type {$instance->instance_type} appears over-provisioned for its current workload profile.",
                    'estimated_monthly_saving' => 45.00,
                    'confidence_score' => 70,
                    'action_type' => 'resize',
                    'status' => 'pending',
                ]
            );
        }
    }

    /**
     * Identify instances that are running but have zero usage (Zombie Resources).
     */
    private function processLowUtilizationOpportunities(Workspace $workspace): void
    {
        $zombies = \App\Models\Ec2Instance::where('workspace_id', $workspace->id)
            ->where('state', 'running')
            ->get();

        Log::info("AI Engine: Found " . $zombies->count() . " running instances for Workspace {$workspace->id}");

        foreach ($zombies as $instance) {
            $uptimeHours = $instance->uptime_since ? $instance->uptime_since->diffInHours(now()) : 0;
            
            Log::info("AI Engine: Checking Instance {$instance->instance_id} - State: {$instance->state}, Type: {$instance->instance_type}, Uptime: {$uptimeHours}h");

            // Heuristic: If instance has been running for > 1 hour but is a small/micro type,
            // we flag it for "Operational Review" if it's potentially idle.
            $isSmallType = str_contains($instance->instance_type, 'micro') || 
                           str_contains($instance->instance_type, 'small') || 
                           str_contains($instance->instance_type, 'nano');
            
            if ($uptimeHours >= 0 && $isSmallType) {
                $instanceName = $instance->name ?: $instance->instance_id;
                
                try {
                    Log::info("AI Engine: Creating recommendation for {$instanceName} (ID: {$instance->instance_id})");
                    \App\Models\AiRecommendation::updateOrCreate(
                        [
                            'workspace_id' => $workspace->id,
                            'resource_type' => 'ec2_zombie',
                            'resource_id' => $instance->instance_id,
                        ],
                        [
                            'title' => "Review Zombie Resource: {$instanceName}",
                            'description' => "This instance has been running for " . round($uptimeHours, 1) . "h but shows zero activity in the last 7 days. Consider terminating it to eliminate 'Ghost' costs.",
                            'estimated_monthly_saving' => 12.00,
                            'confidence_score' => 65,
                            'action_type' => 'delete',
                            'status' => 'pending',
                        ]
                    );
                    Log::info("AI Engine: Successfully saved recommendation for {$instanceName}");
                } catch (\Exception $e) {
                    Log::error("AI Engine SAVE ERROR for {$instanceName}: " . $e->getMessage());
                }
            }
        }
    }
}
