<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function show()
    {
        $edition = Edition::with([
            'speakers',
            'sponsors' => [
                'company' => [
                    'user',
                ],
            ],
        ])->first();

        return Inertia::render('Home', [
            'edition' => $edition,
        ]);
    }
}
