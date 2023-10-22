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
        // This must be separated as drops and creates of the same column cannot be done in the function
        Schema::table('quests', function (Blueprint $table) {
            $table->dropMorphs('requirement');
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->nullableMorphs('requirement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This must be separated as drops and creates of the same column cannot be done in the function
        Schema::table('quests', function (Blueprint $table) {
            $table->dropMorphs('requirement');
        });

        Schema::table('quests', function (Blueprint $table) {
            $table->morphs('requirement');
        });
    }
};
