<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rds_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('db_instance_id', 100)->index();
            $table->string('db_name')->nullable();
            $table->string('db_engine', 50);
            $table->string('instance_class', 50);
            $table->enum('status', ['available', 'stopped', 'starting', 'stopping', 'creating', 'deleting', 'modifying'])->default('stopped');
            $table->string('endpoint')->nullable();
            $table->unsignedSmallInteger('port')->default(3306);
            $table->decimal('cost_per_hour', 10, 4)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'db_instance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rds_instances');
    }
};
