<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $multipliers = [
            '1' => 3.0,
            '2' => 1.5,
            '3' => 1.1,
            '4' => 1.0,
            '5' => 1.0,
        ];

        DB::table('arcade_games')
            ->where('slug', 'mastermind')
            ->orderBy('id')
            ->get()
            ->each(function (object $game) use ($multipliers): void {
                $config = json_decode($game->payout_config ?? '{}', true) ?: [];
                $config['attempt_multipliers'] = $multipliers;

                $update = ['payout_config' => json_encode($config)];

                if ($game->daily_win_cap === null) {
                    $update['daily_win_cap'] = 300;
                }

                DB::table('arcade_games')->where('id', $game->id)->update($update);
            });
    }

    public function down(): void
    {
        $multipliers = [
            '1' => 6.0,
            '2' => 4.0,
            '3' => 2.5,
            '4' => 1.8,
            '5' => 1.5,
        ];

        DB::table('arcade_games')
            ->where('slug', 'mastermind')
            ->orderBy('id')
            ->get()
            ->each(function (object $game) use ($multipliers): void {
                $config = json_decode($game->payout_config ?? '{}', true) ?: [];
                $config['attempt_multipliers'] = $multipliers;

                DB::table('arcade_games')->where('id', $game->id)->update([
                    'payout_config' => json_encode($config),
                ]);
            });
    }
};
