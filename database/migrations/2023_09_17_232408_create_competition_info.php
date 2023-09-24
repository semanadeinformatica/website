<?php

use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Edition;
use App\Models\Participant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function DatabaseHelpers\createManyToManyRelation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->text('theme');
            $table->timestamps();
        });

        Schema::create('competition_teams', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->integer('points');
            $table->foreignIdFor(Competition::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        createManyToManyRelation(CompetitionTeam::class, Participant::class);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_team_participant');
        Schema::dropIfExists('competition_teams');
        Schema::dropIfExists('competitions');
    }
};
