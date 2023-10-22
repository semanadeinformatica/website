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

        if ($enrollment === null) {
            return redirect()->back()->dangerBanner('Participante não inscrito nesta edição!');
        }

        if (! $quest->requirement) {
            $enrollment->quests()->attach($quest);

            return redirect()->back()->banner('Tarefa atribuída com sucesso!');
        }

        if (Gate::denies('give', [$quest, $enrollment])) {
            return redirect()->back()->dangerBanner('Não foi possível atribuir a tarefa ao participante!');
        }

        $enrollment->quests()->attach($quest);

        return redirect()->back()->banner('Tarefa atribuída com sucesso!');
    }
}
