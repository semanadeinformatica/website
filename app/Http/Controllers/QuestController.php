<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        /** @var User|null */
        $user = $request->user();

        if ($user === null) {
            Log::warning('Unauthenticated user attempted to give quest to participant');

            return redirect()->back()->dangerBanner('Inicia sessão para efetuar esta ação');
        }

        if ($user->cannot('give', [$quest, $enrollment])) {
            Log::warning('Current user is not allowed to give quest "{quest}" to user {user}', [
                'quest' => $quest->name,
                'user' => $enrollment->participant->usertype->name,
            ]);

            return redirect()->back()->dangerBanner('Não foi possível atribuir a tarefa ao participante!');
        }

        $enrollment->quests()->attach($quest);
        Log::info('Quest {quest} given to user {user}', [
            'quest' => $quest->name,
            'user' => $enrollment->participant->usertype->name,
        ]);

        return redirect()->back()->banner('Tarefa atribuída com sucesso!');
    }
}
