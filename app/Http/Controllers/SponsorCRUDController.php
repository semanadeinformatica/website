<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;

class SponsorCRUDController extends CRUDController
{
    protected array $rules = [
        'tier' => 'required|in:platinum,gold,silver',
        'edition_id' => 'required|exists:editions,id',
        'company_id' => 'required|exists:companies,id',
    ];

    protected array $search = ['tier'];

    protected function created(array $new): ?array
    {
        return [
            'edition_id' => $new['edition_id'],
            'company_id' => $new['company_id'],
            'tier' => strtoupper($new['tier']),
        ];
    }

    protected function updated($old, array $new): ?array
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
