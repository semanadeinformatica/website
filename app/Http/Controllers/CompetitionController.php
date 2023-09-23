<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class CompetitionController extends Controller
{
    /**
     * Renders the given competition.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Competition $competition)
    {
        return Inertia::render('Competition', [
            'competition' => $competition,
        ]);
    }
}
