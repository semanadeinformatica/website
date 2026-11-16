<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Quest;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestController extends Controller
{
    public function give(Request $request, Quest $quest)
    {
        $request->validate([
            'quest_code' => 'required|string|exists:participants,quest_code',
        ], [
            'quest_code.required' => 'Introduz o código do participante.',
            'quest_code.exists' => 'Código inválido ou expirado. Pede ao participante para gerar um novo QR code.',
        ]);

        /** @var Edition|null $edition */
        $edition = $request->input('edition');

        $participant = Participant::firstWhere('quest_code', $request->get('quest_code'));

        if ($participant === null) {
            return redirect()->back()->dangerBanner(
                'Código inválido ou expirado. Pede ao participante para gerar um novo QR code.'
            );
        }

        $enrollment = $participant->enrollments()->where('edition_id', $edition?->id)->first();

        if ($enrollment === null) {
            return redirect()->back()->dangerBanner(
                $edition === null
                    ? 'O participante não se inscreveu no SINF.'
                    : "O participante não se inscreveu no {$edition->name}."
            );
        }

        /** @var User|null */
        $user = $request->user();

        if ($user === null) {
            Log::warning('Unauthenticated user attempted to give quest to participant');

            return redirect()->back()->dangerBanner('Inicia sessão para efetuar esta ação');
        }

        if ($enrollment->quests()->where('quest_id', $quest->id)->exists()) {
            return redirect()->back()->dangerBanner(
                "O participante já realizou a tarefa \"{$quest->name}\"."
            );
        }

        if (! $this->canOperateQuest($user, $quest, $enrollment)) {
            Log::warning('Current user is not allowed to give quest "{quest}" to user {user}', [
                'quest' => $quest->name,
                'user' => $enrollment->participant->user->name,
            ]);

            return redirect()->back()->dangerBanner(
                'Não tens permissão para atribuir esta tarefa a este participante.'
            );
        }

        if ($this->missingRequiredEventEnrollment($quest, $enrollment)) {
            return redirect()->back()->dangerBanner(
                $this->missingRequiredEventEnrollmentMessage($quest)
            );
        }

        // Keep the policy as the final authority in case the checks above drift.
        if ($user->cannot('give', [$quest, $enrollment])) {
            Log::warning('Current user is not allowed to give quest "{quest}" to user {user}', [
                'quest' => $quest->name,
                'user' => $enrollment->participant->user->name,
            ]);

            return redirect()->back()->dangerBanner(
                'Não foi possível atribuir a tarefa ao participante.'
            );
        }

        $enrollment->quests()->attach($quest);

        Log::info('Quest {quest} given to user {user}', [
            'quest' => $quest->name,
            'user' => $enrollment->participant->user->name,
        ]);

        return redirect()->back()->banner('Tarefa atribuída com sucesso!');
    }

    private function canOperateQuest(User $user, Quest $quest, Enrollment $enrollment): bool
    {
        if ($user->isAdmin() || $user->isStaff($enrollment->edition)) {
            return in_array($quest->requirement_type, [Event::class, Stand::class, null], true);
        }

        return $user->isCompany()
            && $quest->requirement_type === Stand::class
            && $quest->requirement?->sponsor?->company?->is($user->usertype);
    }

    private function missingRequiredEventEnrollment(Quest $quest, Enrollment $enrollment): bool
    {
        return $quest->requirement_type === Event::class
            && ! $enrollment->events()->where('event_id', $quest->requirement_id)->exists();
    }

    private function missingRequiredEventEnrollmentMessage(Quest $quest): string
    {
        /** @var Event|null $event */
        $event = $quest->requirement;

        if ($event === null) {
            return 'O participante não está inscrito no evento necessário para esta tarefa.';
        }

        $type = $event->type?->name;

        return $type === null
            ? "O participante não está inscrito no evento \"{$event->name}\"."
            : "O participante não está inscrito no evento \"{$event->name}\" ({$type}).";
    }
}
