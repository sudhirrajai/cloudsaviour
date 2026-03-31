<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type', 50);
            $table->string('resource_id', 100);
            $table->string('title');
            $table->text('description');
            $table->decimal('estimated_monthly_saving', 10, 2)->default(0);
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->enum('action_type', ['resize', 'delete', 'schedule', 'lifecycle']);
            $table->json('action_payload')->nullable();
            $table->enum('status', ['pending', 'applied', 'dismissed'])->default('pending');
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
