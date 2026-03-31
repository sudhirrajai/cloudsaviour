<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->text('aws_access_key')->nullable();
            $table->text('aws_secret_key')->nullable();
            $table->string('aws_region', 50)->default('ap-south-1');
            $table->string('aws_account_id', 30)->nullable();
            $table->enum('plan', ['free', 'developer', 'team'])->default('free');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
