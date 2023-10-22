<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        $totalDays = $edition->event_days()->count();

        $validator = Validator::make($request->all(), [
            'day' => 'sometimes|integer|min:1|max:'.$totalDays,
        ], [
            'integer' => 'The :attribute value must be an integer.',
            'min' => 'The :attribute value must be at least :min.',
            'max' => 'The :attribute value must be at most :max.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();

            if ($totalDays === 0) {
                return Inertia::render('Program', [
                    'eventDay' => fn () => null,
                    'queryDay' => fn () => 0,
                    'totalDays' => fn () => 0,
                ]);
            }

            return redirect()->route('home')->dangerBanner($errors->first('day'));
        }

        $validated = $validator->validated();
        $queryDay = isset($validated['day']) ? $validated['day'] : $this->getCurrentProgramDay($edition);

        /** @var \App\Models\EventDay|null $eventDay */
        $eventDay = $edition->event_days()->orderBy('date', 'ASC')->skip($queryDay - 1)->first();

        return Inertia::render('Program', [
            'eventDay' => fn () => $eventDay?->load([
                'stands' => [
                    'sponsor' => [
                        'company' => [
                            'user',
                        ],
                        'tier',
                    ],
                ],
                'talks' => [
                    'users',
                ],
                'activities' => [
                    'users',
                ],
            ]),
            'queryDay' => fn () => intval($queryDay),
            'totalDays' => fn () => intval($totalDays),
        ]);
    }

    private function getCurrentProgramDay(Edition $edition)
    {
        $today = Carbon::today();
        $dates = $edition->event_days()->orderBy('date', 'ASC')->pluck('date');

        $currentDayId = key($dates->filter(fn ($date) => $date == $today)->all());

        return $currentDayId ? $currentDayId + 1 : ProgramController::DEFAULT_PROGRAM_DAY;
    }
}
