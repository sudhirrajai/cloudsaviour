<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('resource_type', ['ec2', 'rds']);
            $table->string('resource_id', 100);
            $table->enum('action', ['start', 'stop']);
            $table->string('cron_expression')->nullable();
            $table->json('days_of_week');
            $table->string('time_of_day', 5); // HH:MM
            $table->string('timezone', 50)->default('Asia/Kolkata');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
