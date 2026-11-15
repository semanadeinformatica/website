<?php

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\RaffleDraw;
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
        Schema::create('raffle_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('ticket_price')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('draw_at')->nullable();
            $table->foreignId('winner_enrollment_id')
                ->nullable()
                ->constrained('enrollments')
                ->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('raffle_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RaffleDraw::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Enrollment::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['raffle_draw_id', 'enrollment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raffle_tickets');
        Schema::dropIfExists('raffle_draws');
    }
};
