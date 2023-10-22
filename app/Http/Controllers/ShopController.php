<?php

namespace App\Http\Controllers;

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
}
