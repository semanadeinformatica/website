<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function show(Request $request)
    {
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        // We can do this here since we already fetched the edition from the DB, so we would not gain any performance from "short-circuiting" the validation
        $eventDays = $edition->event_days;
        $totalDays = count($eventDays);

        $validated = $request->validate([
            'day' => 'required|integer|min:1|max:'.$totalDays,
        ]);
        $queryDay = $validated['day'];

        $eventDay = $eventDays->get($queryDay - 1);

        return Inertia::render('Program', [
            'eventDay' => fn () => $eventDay->toArray(),
            'queryDay' => fn () => intval($queryDay),
            'totalDays' => fn () => intval($totalDays),
        ]);
    }
}
