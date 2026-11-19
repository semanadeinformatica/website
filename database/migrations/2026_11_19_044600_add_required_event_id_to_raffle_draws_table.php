<?php

use App\Models\Event;
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
        Schema::table('raffle_draws', function (Blueprint $table) {
            $table->foreignIdFor(Event::class, 'required_event_id')
                ->nullable()
                ->after('edition_id')
                ->constrained('events')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raffle_draws', function (Blueprint $table) {
            $table->dropConstrainedForeignId('required_event_id');
        });
    }
};
