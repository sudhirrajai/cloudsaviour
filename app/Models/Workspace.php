<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    protected $fillable = [
        'name', 'slug', 'owner_id',
        'aws_access_key', 'aws_secret_key', 'aws_region', 'aws_account_id',
        'plan', 'plan_id', 'is_active', 'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    // AWS credentials are encrypted at rest
    public function setAwsAccessKeyAttribute(?string $value): void
    {
        $this->attributes['aws_access_key'] = $value ? encrypt($value) : null;
    }

    public function getAwsAccessKeyAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function setAwsSecretKeyAttribute(?string $value): void
    {
        $this->attributes['aws_secret_key'] = $value ? encrypt($value) : null;
    }

    public function getAwsSecretKeyAttribute(?string $value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function hasAwsCredentials(): bool
    {
        return !empty($this->attributes['aws_access_key']) && !empty($this->attributes['aws_secret_key']);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role', 'invited_by', 'joined_at')
            ->withTimestamps();
    }

    public function ec2Instances(): HasMany
    {
        return $this->hasMany(Ec2Instance::class);
    }

    public function rdsInstances(): HasMany
    {
        return $this->hasMany(RdsInstance::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function idleResources(): HasMany
    {
        return $this->hasMany(IdleResource::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function costRecords(): HasMany
    {
        return $this->hasMany(CostRecord::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class);
    }
}
