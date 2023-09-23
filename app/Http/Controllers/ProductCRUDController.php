<?php

namespace App\Http\Controllers;

use App\Models\Edition;

class ProductCRUDController extends CRUDController
{
    protected array $rules = [
        'name' => 'required|string',
        'price' => 'required|integer',
        'stock' => 'required|integer',
        'edition_id' => 'required|exists:editions,id',
    ];

    protected array $search = ['name', 'price', 'stock'];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }
}
