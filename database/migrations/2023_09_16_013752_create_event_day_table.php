<?php

use App\Models\Edition;
use App\Models\EventDay;
use App\Models\Sponsor;
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
        Schema::create('event_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('theme');
            $table->timestamps();
        });

        Schema::create('stands', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventDay::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Sponsor::class)->constrained()->cascadeOnDelete();
            // $table->unique(['event_day_id', 'sponsor_id']); // TODO: this breaks seeders
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Edition::class);
            $table->dropColumn(['date_start', 'date_end']);
            $table->foreignIdFor(EventDay::class)->constrained()->cascadeOnDelete();
            $table->time('time_start');
            $table->time('time_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(EventDay::class);
            $table->dropColumn(['time_start', 'time_end']);
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->dateTime('date_start');
            $table->dateTime('date_end');
        });

        Schema::dropIfExists('stands');
        Schema::dropIfExists('event_days');
    }
};
