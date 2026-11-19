<?php

namespace App\Http\Controllers;

use App\Models\Edition;
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
        /** @var Edition|null $edition */
        $edition = $request->input('edition');
        /** @var User|null $user */
        $user = $request->user();
        $boughtProductIds = [];
        $isParticipant = $user?->isParticipant() ?? false;
        $isEnrolled = false;
        $points = null;

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $canManageShop = Gate::allows('admin') || Gate::allows('staff', [$edition]);

        if (! $edition->shop_enabled && ! $canManageShop) {
            return redirect()->route('home')->dangerBanner('A loja encontra-se temporariamente indisponível.');
        }

        if ($user !== null && $isParticipant) {
            $enrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first();

            // This makes one less query to the DB ☝️🤓
            $isEnrolled = $enrollment !== null;
            $points = $enrollment?->points;

            if ($enrollment) {
                $boughtProductIds = $enrollment->products()->pluck('products.id')->all();
            }
        }

        $finalPrizes = $edition->finalPrizes()->get();

        $products = $edition->products()->reorder('price', 'desc')->get()->each(function (Product $product) use ($user, $boughtProductIds) {
            $product->alreadyBought = in_array($product->id, $boughtProductIds);
            $product->canBeBought =
                $product->stock <= 0
                    ? false
                    : $user?->can('buy', $product) ?? true;
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
            'finalPrizes' => $finalPrizes,
            'points' => $points,
            'isEnrolled' => $isEnrolled,
            'isParticipant' => $isParticipant,
            'shopEnabled' => $edition->shop_enabled,
        ]);
    }

    public function buyProduct(Request $request, Product $product)
    {
        /** @var Edition|null $edition */
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        if (! $edition->shop_enabled) {
            return redirect()->back()->dangerBanner('A loja encontra-se temporariamente indisponível.');
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

        $enrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first();

        if ($enrollment === null) {
            return redirect()->back()->dangerBanner('Não estás inscrito nesta edição');
        }

        $enrollment->products()->attach($product->id, ['price' => $product->price]);
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
