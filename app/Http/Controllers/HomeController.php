<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function show(Request $request)
    {
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $sponsors = $edition->sponsors()->with(['company' => ['user']])->get();
        $speakers = $edition->speakers;

        $event_count = $edition->events()->count();
        $activity_count = $edition->events()->where('capacity', '>', 0)->count();
        $talk_count = $event_count - $activity_count;
        $day_count = $edition->event_days()->count();
        $stand_count = $edition->stands()->count();

        return Inertia::render('Home', [
            'edition' => $edition,
            'sponsors' => $sponsors,
            'speakers' => $speakers,
            'activityCount' => $activity_count,
            'talkCount' => $talk_count,
            'dayCount' => $day_count,
            'standCount' => $stand_count,
        ]);
    }
}
