<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionTeam;
use App\Models\Edition;

class CompetitionCRUDController extends CRUDController
{
    protected string $model = Competition::class;

    protected string $view = 'Competition';

    protected array $rules = [
        'theme' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date',
        'edition_id' => 'required|integer|exists:editions,id',
    ];

    protected array $search = ['theme', 'date_start', 'date_end'];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
