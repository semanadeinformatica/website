<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\SponsorTier;

class SponsorTierCRUDController extends CRUDController
{
    protected string $model = SponsorTier::class;

    protected string $view = 'SponsorTier';

    protected array $rules = [
        'name' => 'required|string',
        'color' => 'required|string',
        'rank' => 'required|integer',
        'canSeeCV' => 'required|boolean',
        'canSeeLinkedin' => 'required|boolean',
        'canSeeAll' => 'required|boolean',
        'edition_id' => 'required|exists:editions,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
