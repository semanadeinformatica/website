<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Sponsor;

class SponsorCRUDController extends CRUDController
{
    protected string $model = Sponsor::class;

    protected string $view = 'Sponsor';

    protected array $rules = [
        'tier' => 'required|in:platinum,gold,silver',
        'edition_id' => 'required|exists:editions,id',
        'company_id' => 'required|exists:companies,id',
    ];

    protected function created(array $new): ?array
    {
        return [
            'edition_id' => $new['edition_id'],
            'company_id' => $new['company_id'],
            'tier' => strtoupper($new['tier']),
        ];
    }

    protected function updated(array $old, array $new): ?array
    {
        return [
            'edition_id' => $new['edition_id'],
            'company_id' => $new['company_id'],
            'tier' => strtoupper($new['tier']),
        ];
    }

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'companies' => Company::with('user')->get(),
        ];
    }
}
