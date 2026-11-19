<?php

use App\Models\Event;
use App\Models\Quest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('raffle_draws', function (Blueprint $table) {
            $table->foreignIdFor(Quest::class, 'required_quest_id')
                ->nullable()
                ->after('required_event_id')
                ->constrained('quests')
                ->nullOnDelete();
        });

        // Keep existing draws working: use the quest tied to the event that
        // was previously detected for them. Admins can change the task later.
        DB::table('raffle_draws')
            ->whereNotNull('required_event_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $draw): void {
                $questId = DB::table('quests')
                    ->where('requirement_type', Event::class)
                    ->where('requirement_id', $draw->required_event_id)
                    ->orderBy('id')
                    ->value('id');

                if ($questId !== null) {
                    DB::table('raffle_draws')
                        ->where('id', $draw->id)
                        ->update(['required_quest_id' => $questId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raffle_draws', function (Blueprint $table) {
            $table->dropConstrainedForeignId('required_quest_id');
        });
    }
};
