<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
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
            $isEnrolled = $user->usertype->enrollments()->where('edition_id', $edition->id)->exists();
            $points = $user->usertype->enrollments()->where('edition_id', $edition->id)->first()->points;
        }

        if ($edition === null) {
            return response('No edition found', 500);
        }

        return Inertia::render('Shop', [
            'products' => $edition->products,
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
            return redirect()->back()->dangerBanner('You have to be logged in to perform this action');
        }

        if (! $user->isParticipant()) {
            return redirect()->back()->dangerBanner('You are not a participant');
        }

        /** @var Enrollment|null */
        $currentEnrollment = $user->usertype->enrollments()->where('edition_id', $edition->id)->first();

        if ($currentEnrollment === null) {
            return redirect()->back()->dangerBanner('You are not enrolled in this edition');
        }

        $points = $currentEnrollment->points;

        if ($points < $product->price) {
            return redirect()->back()->dangerBanner('You do not have enough points');
        }

        $currentEnrollment->products()->attach($product->id);

        return redirect()->back()->banner('Product bought');
    }
}
