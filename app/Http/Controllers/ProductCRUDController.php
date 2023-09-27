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
        'image' => 'nullable|mimes:jpg,jpeg,png|max:1024',
    ];

    protected array $search = ['name', 'price', 'stock'];

    protected function with(): array
    {
        return [
            'editions' => Edition::all(),
        ];
    }

    protected function created(array $new): ?array
    {
        $product = Product::create([
            'name' => $new['name'],
            'price' => $new['price'],
            'stock' => $new['stock'],
            'edition_id' => $new['edition_id'],
        ]);

        if (isset($new['image'])) {
            $product->updateImageProduct($new['image']);
        }

        return null;
    }

    protected function updated($old, array $new): ?array
    {

        if (isset($new['image'])) {
            $old->updateImageProduct($new['image']);
        }

        return [
            'name' => $new['name'],
            'price' => $new['price'],
            'stock' => $new['stock'],
            'edition_id' => $new['edition_id'],
        ];
    }
}
