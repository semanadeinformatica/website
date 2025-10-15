<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CompetitionPrize;
use App\Models\Competition;

class CompetitionPrizeCRUDController extends CRUDController
{
    protected string $model = CompetitionPrize::class;

    protected string $view = 'CompetitionPrizes';

    protected array $rules = [
        'place' => 'required|integer|min:1|max:3',
        'prize_picture' => 'nullable|mimes:jpg,jpeg,png|max:10240',
        'competition_id' => 'required|integer|exists:competitions,id',
    ];

    protected function with(): array
    {
        return [
            'competitions' => Competition::with('edition')->get(),
        ];
    }

}
