<?php

namespace App\Http\Controllers;

use App\Models\CompetitionTeam;
use App\Models\Student;

class CompetitionTeamCRUDController extends CRUDController
{
    protected string $model = CompetitionTeam::class;

    protected string $view = 'CompetitionTeam';

    protected array $rules = [
        'name' => 'required|string',
    ];

    protected array $search = ['name'];

    protected function with(): array
    {
        return [
            'students' => Student::all(),
        ];
    }
}
