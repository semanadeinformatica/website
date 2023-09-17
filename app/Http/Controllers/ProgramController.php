<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProgramController extends Controller
{
    const DEFAULT_PROGRAM_DAY = 1;

    public function show(Request $request)
    {
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        // We can do this here since we already fetched the edition from the DB, so we would not gain any performance from "short-circuiting" the validation
        $eventDays = $edition->event_days()->orderBy('date', 'ASC')->get();
        $totalDays = count($eventDays);

        $validator = Validator::make($request->all(), [
            'day' => 'sometimes|integer|min:1|max:'.$totalDays,
        ], [
            'integer' => 'The :attribute value must be an integer.',
            'min' => 'The :attribute value must be at least :min.',
            'max' => 'The :attribute value must be at most :max.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            return redirect()->route('home')->dangerBanner($errors->first('day'));
        }

        $validated = $validator->validated();
        $queryDay = isset($validated['day']) ? $validated['day'] : ProgramController::DEFAULT_PROGRAM_DAY;

        $eventDay = $eventDays->get($queryDay - 1);

        return Inertia::render('Program', [
            'eventDay' => fn () => $eventDay,
            'queryDay' => fn () => intval($queryDay),
            'totalDays' => fn () => intval($totalDays),
        ]);
    }
}
