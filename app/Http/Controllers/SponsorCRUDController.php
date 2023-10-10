<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Sponsor;
use App\Models\SponsorTier;

class SponsorCRUDController extends CRUDController
{
    protected string $model = Sponsor::class;

    protected string $view = 'Sponsor';

    protected array $rules = [
        'sponsor_tier_id' => 'required|exists:editions,id',
        'edition_id' => 'required|exists:editions,id',
        'company_id' => 'required|exists:companies,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'tiers' => SponsorTier::all(),
            'companies' => Company::with('user')->get(),
        ];
    }
}
