<?php
use App\Models\Workspace;
use App\Services\Aws\AiEngineService;
use App\Models\AiRecommendation;
use App\Models\Ec2Instance;

$workspace = Workspace::find(2);
if (!$workspace) {
    echo "Workspace 2 not found\n";
    exit(1);
}

echo "Workspace ID: " . $workspace->id . " | Name: " . $workspace->name . "\n";
$instances = $workspace->ec2Instances()->where('state', 'running')->get();
echo "Running Instances Count: " . $instances->count() . "\n";

foreach ($instances as $i) {
    $uptimeHours = $i->uptime_since ? $i->uptime_since->diffInHours(now()) : 0;
    echo " - ID: {$i->id} | {$i->instance_id} | Type: {$i->instance_type} | Uptime: {$uptimeHours}h\n";
    
    $isSmallType = str_contains($i->instance_type, 'micro') || 
                   str_contains($i->instance_type, 'small') || 
                   str_contains($i->instance_type, 'nano');
    echo "   Criteria: Uptime > 1: " . ($uptimeHours > 1 ? 'YES' : 'NO') . " | isSmall: " . ($isSmallType ? 'YES' : 'NO') . "\n";
    
    if ($uptimeHours > 1 && $isSmallType) {
        echo "   --- THIS SHOULD GENERATE A RECOMMENDATION ---\n";
        try {
            $rec = AiRecommendation::updateOrCreate(
                [
                    'workspace_id' => $workspace->id,
                    'resource_type' => 'ec2_zombie',
                    'resource_id' => $i->instance_id,
                ],
                [
                    'title' => 'Debug Title',
                    'description' => 'Debug Description',
                    'estimated_monthly_saving' => 12.00,
                    'confidence_score' => 65,
                    'action_type' => 'delete',
                    'status' => 'pending',
                ]
            );
            echo "   Created ID: {$rec->id}\n";
        } catch (\Exception $e) {
            echo "   SAVE ERROR: " . $e->getMessage() . "\n";
        }
    }
}
