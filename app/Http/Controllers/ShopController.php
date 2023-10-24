<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function show(Request $request)
    {
        $edition = $request->input('edition');
        $user = $request->user();

        if ($user && $user->isParticipant()) {
            $isParticipant = $user->isParticipant();
            $enrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first();

            // This makes one less query to the DB ☝️🤓
            $isEnrolled = $enrollment !== null;
            $points = $enrollment?->points;
        }

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $products = $edition->products()->orderBy('price')->get()->each(function (Product $product) use ($user) {
            $product->canBeBought = $user ? $user->can('buy', $product) : true;
        });

        return Inertia::render('Shop', [
            'products' => $products,
            'points' => $points ?? null,
            'isEnrolled' => $isEnrolled ?? null,
            'isParticipant' => $isParticipant ?? null,
        ]);
    }

    public function buyProduct(Request $request, Product $product)
    {
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        /** @var User|null */
        $user = $request->user();

        if ($user === null) {
            return redirect()->back()->dangerBanner('Tens que ter sessão iniciada para poderes comprar este produto');
        }

        if ($user->cannot('buy', $product)) {
            return redirect()->back()->dangerBanner('Não podes comprar este produto');
        }

        $user->usertype->enrollments()->where('edition_id', $edition->id)->first()->products()->attach($product);
        $product->decrement('stock', 1);

        return redirect()->back()->banner('Produto comprado');
    }
}
