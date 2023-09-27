<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function show(Request $request)
    {
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $products = Product::where('edition_id', $edition->id)->get();

        return Inertia::render('Shop', [
            'products' => $products,
        ]);
    }
}
