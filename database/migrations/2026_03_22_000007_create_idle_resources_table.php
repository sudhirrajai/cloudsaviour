<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idle_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type', 50); // ebs_volume, elastic_ip, nat_gateway, snapshot, load_balancer
            $table->string('resource_id', 100);
            $table->string('resource_name')->nullable();
            $table->json('details')->nullable();
            $table->decimal('estimated_monthly_cost', 10, 2)->default(0);
            $table->timestamp('detected_at');
            $table->boolean('is_ignored')->default(false);
            $table->timestamp('ignored_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idle_resources');
    }
};
