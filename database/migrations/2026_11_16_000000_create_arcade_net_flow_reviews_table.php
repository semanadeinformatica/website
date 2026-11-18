<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arcade_net_flow_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('low_enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();
            $table->foreignId('high_enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();
            $table->string('status', 16)->default('open');
            $table->text('note')->nullable();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('reviewed_match_count')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['low_enrollment_id', 'high_enrollment_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arcade_net_flow_reviews');
    }
};
