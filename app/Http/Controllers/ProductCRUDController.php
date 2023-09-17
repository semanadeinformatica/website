<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Product;

class ProductCRUDController extends CRUDController
{
    protected string $model = Product::class;

    protected string $view = 'Product';

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
