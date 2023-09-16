<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|integer|min:1',
        ]);
        $queryDay = $validated['day'];

        $edition = $request->input('edition');

        if ($edition === null) {

            return;
        }

        $eventDays = $edition->event_days;

        if ($queryDay > count($eventDays)) {

            // return;
        }

        return Inertia::render('Program', ['eventDays' => $eventDays->toArray()]);
    }
}
