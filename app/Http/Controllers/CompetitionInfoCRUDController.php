<?php

namespace App\Http\Controllers;

use App\Models\CompetitionInfo;
use App\Models\CompetitionTeam;
use App\Models\Edition;

class CompetitionInfoCRUDController extends CRUDController
{
    protected string $model = CompetitionInfo::class;

    protected string $view = 'CompetitionInfo';

    protected array $rules = [
        'theme' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date',
        'edition_id' => 'required|integer|in:editions,id',
        'first_place' => 'required|integer|in:competition_teams,id',
        'second_place' => 'required|integer|in:competition_teams,id',
        'third_place' => 'required|integer|in:competition_teams,id',

    ];

    protected array $search = ['theme', 'date_start', 'date_end'];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'competitionTeams' => CompetitionTeam::all(),
        ];
    }
}
