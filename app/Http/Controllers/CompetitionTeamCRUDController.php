<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionTeam;

class CompetitionTeamCRUDController extends CRUDController
{
    protected string $model = CompetitionTeam::class;

    protected string $view = 'CompetitionTeam';

    protected array $rules = [
        'name' => 'required|string',
        'points' => 'required|integer',
        'competition_id' => 'required|integer|exists:competitions,id',
    ];

    protected function with(): array
    {
        return [
            'competitions' => Competition::with('edition')->get(),
        ];
    }
}
