<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arcade_games', function (Blueprint $table) {
            $table->unsignedInteger('daily_bet_limit')
                ->nullable()
                ->after('daily_win_cap');
        });
    }

    public function down(): void
    {
        Schema::table('arcade_games', function (Blueprint $table) {
            $table->dropColumn('daily_bet_limit');
        });
    }
};
