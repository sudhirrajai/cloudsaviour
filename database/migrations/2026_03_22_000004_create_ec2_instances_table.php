<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ec2_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('instance_id', 50)->index();
            $table->string('name')->nullable();
            $table->string('instance_type', 50);
            $table->enum('state', ['running', 'stopped', 'pending', 'stopping', 'shutting-down', 'terminated'])->default('stopped');
            $table->string('public_ip', 45)->nullable();
            $table->string('private_ip', 45)->nullable();
            $table->string('region', 30);
            $table->string('availability_zone', 30);
            $table->decimal('cost_per_hour', 10, 4)->default(0);
            $table->timestamp('uptime_since')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'instance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ec2_instances');
    }
};
