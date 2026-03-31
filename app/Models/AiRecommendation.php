<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    protected $fillable = [
        'workspace_id', 'resource_type', 'resource_id', 'title', 'description',
        'estimated_monthly_saving', 'confidence_score', 'action_type',
        'action_payload', 'status', 'applied_at',
    ];

    protected $casts = [
        'estimated_monthly_saving' => 'decimal:2',
        'confidence_score' => 'integer',
        'action_payload' => 'array',
        'applied_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
