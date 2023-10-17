<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Edition;

class CompetitionCRUDController extends CRUDController
{
    protected string $model = Competition::class;

    protected string $view = 'Competition';

    protected array $rules = [
        'theme' => 'required|string',
        'date_start' => 'required|date',
        'date_end' => 'required|date|after:date_start',
        'edition_id' => 'required|integer|exists:editions,id',
        'name' => 'required|string',
        'slug' => 'required|string',
        'registration_link' => 'required|url',
        'regulation' => 'required|string',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
