<?php

use App\Models\CompetitionInfo;
use App\Models\CompetitionTeam;
use App\Models\Edition;
use App\Models\Student;
use function DatabaseHelpers\createManyToManyRelation;
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
        Schema::create('competition_teams', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->foreignIdFor(CompetitionInfo::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        createManyToManyRelation(CompetitionTeam::class, Student::class);

        Schema::create('competition_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Edition::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(CompetitionTeam::class, 'first_place')->constrained('competition_teams')/* ->cascadeOnDelete() */;
            $table->foreignIdFor(CompetitionTeam::class, 'second_place')->constrained('competition_teams')/* ->cascadeOnDelete() */;
            $table->foreignIdFor(CompetitionTeam::class, 'third_place')->constrained('competition_teams')/* ->cascadeOnDelete() */;
            $table->datetime('date_start');
            $table->datetime('date_end');
            $table->text('theme');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_infos');
        Schema::dropIfExists('competition_info_student');
        Schema::dropIfExists('competition_teams');
    }
};
