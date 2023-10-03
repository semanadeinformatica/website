<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Quest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class QuestController extends Controller
{
    public function give(Request $request, Quest $quest)
    {
        $request->validate([
            'quest_code' => 'required|exists:participants,quest_code',
        ]);

        $edition = $request->input('edition');
        $participant = Participant::firstWhere('quest_code', $request->get('quest_code'));
        $enrollment = $participant->enrollments()->where('edition_id', $edition?->id)->first();

        Gate::authorize('give', [$quest, $enrollment]);

        $enrollment->quests()->attach($quest);

        return redirect()->back()->banner('Quest atribuída com sucesso!');
    }
}
