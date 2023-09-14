<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Edition;
use App\Models\Product;

class ProductCRUDController extends CRUDController
{
    protected string $model = Sponsor::class;

    protected string $view = 'Sponsor';

    protected array $rules = [
        'tier' => 'required|in:platinum,gold,silver',
        'edition_id' => 'required|exists:editions,id',
        'company_id' => 'required|exists:companies,id',
    ];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
            'companies' => Company::all(),
        ];
    }
}
