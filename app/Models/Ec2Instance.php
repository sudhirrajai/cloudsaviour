<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ec2Instance extends Model
{
    protected $fillable = [
        'workspace_id', 'instance_id', 'name', 'instance_type', 'state',
        'public_ip', 'private_ip', 'region', 'availability_zone',
        'cost_per_hour', 'uptime_since', 'last_synced_at',
    ];

    protected $casts = [
        'cost_per_hour' => 'decimal:4',
        'uptime_since' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isRunning(): bool
    {
        return $this->state === 'running';
    }
}
