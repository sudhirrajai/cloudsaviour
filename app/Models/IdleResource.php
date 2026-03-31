<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdleResource extends Model
{
    protected $fillable = [
        'workspace_id', 'resource_type', 'resource_id', 'resource_name',
        'details', 'estimated_monthly_cost', 'detected_at',
        'is_ignored', 'ignored_at', 'resolved_at',
    ];

    protected $casts = [
        'details' => 'array',
        'estimated_monthly_cost' => 'decimal:2',
        'detected_at' => 'datetime',
        'is_ignored' => 'boolean',
        'ignored_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
