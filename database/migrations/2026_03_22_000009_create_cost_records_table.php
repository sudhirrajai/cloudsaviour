<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('service', 100); // EC2, RDS, S3, CloudWatch, etc.
            $table->decimal('amount', 12, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('currency', 3)->default('USD');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'period_start', 'service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_records');
    }
};
