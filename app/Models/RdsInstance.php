<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RdsInstance extends Model
{
    protected $fillable = [
        'workspace_id', 'db_instance_id', 'db_name', 'db_engine',
        'instance_class', 'status', 'endpoint', 'port',
        'cost_per_hour', 'last_synced_at',
    ];

    protected $casts = [
        'port' => 'integer',
        'cost_per_hour' => 'decimal:4',
        'last_synced_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
