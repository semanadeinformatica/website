<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function show(Request $request)
    {
        /** @var Edition */
        $edition = $request->input('edition');

        if ($edition === null) {
            return response('No edition found', 500);
        }

        $sponsors = $edition->sponsors()->with(['company' => ['user']])->get();
        $speakers = $edition->speakers()->get();
        $days = $edition->event_days()->orderBy('date', 'ASC')->get();

        $activity_count = $edition->workshops()->count();
        $talk_count = $edition->talks()->count();
        $stand_count = $edition->stands()->count();

        $can_enroll = Gate::allows('enroll', $edition);

        return Inertia::render('Home', [
            'edition' => $edition,
            'sponsors' => $sponsors,
            'speakers' => $speakers,
            'activityCount' => $activity_count,
            'talkCount' => $talk_count,
            'days' => $days,
            'standCount' => $stand_count,
            'canEnroll' => $can_enroll,
        ]);
    }
}
