<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\EnrollmentProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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

        if (Gate::allows('admin') || Gate::allows('staff', [$edition])) {
            $products->load([
                'enrollments' => [
                    'participant' => ['user'],
                ],
            ]);
        }

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

            Log::warning('User {user} tried to buy product "{product}" but failed', [
                'user' => $user->name,
                'product' => $product->name,
            ]);

            return redirect()->back()->dangerBanner('Não podes comprar este produto');
        }

        $user->usertype->enrollments()->where('edition_id', $edition->id)->first()->products()->attach($product);
        $product->decrement('stock', 1);
        Log::info('User {user_name}[{user_email}] bought product "{product_name}" for {product_price} points', [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'product_name' => $product->name,
            'product_price' => $product->price,
        ]);

        return redirect()->back()->banner('Produto comprado');
    }

    public function redeemProduct(Request $request, Product $product, Enrollment $enrollment)
    {
        /** @var User|null */
        $user = $request->user();

        Gate::authorize('redeem', [$product, $enrollment]);

        EnrollmentProduct::where('enrollment_id', $enrollment->id)->where('product_id', $product->id)->update(['redeemed' => true]);
        Log::info('User {user_name}[{user_email}] redeemed product "{product_name}"', [
            'user_name' => $user->name,
            'user_email' => $user->email,
            'product_name' => $product->name,
        ]);

        return redirect()->back()->banner('Produto entregue');
    }
}
