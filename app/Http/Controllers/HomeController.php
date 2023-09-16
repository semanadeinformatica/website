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

        $event_count = $edition->events()->count();
        $activity_count = $edition->events()->where('capacity', '>', 0)->count();
        $talk_count = $event_count - $activity_count;

        return Inertia::render('Home', [
            'edition' => $edition,
            'activity_count' => $activity_count,
            'talk_count' => $talk_count,
        ]);
    }
}
