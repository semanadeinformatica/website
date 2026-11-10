<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\RaffleDraw;
use App\Models\RaffleTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RaffleController extends Controller
{
    public function index(Request $request): Response
    {
        $edition = $request->input('edition');
        abort_if($edition === null, 404, 'Edição não encontrada');

        $user = $request->user();
        $enrollment = null;

        if ($user && $user->isParticipant()) {
            $enrollment = $user->usertype->enrollments()
                ->where('edition_id', $edition->id)
                ->first();
        }

        $draws = RaffleDraw::query()
            ->where('edition_id', $edition->id)
            ->with(['winner.participant.user:id,name,profile_photo_path'])
            ->withSum('tickets as total_tickets', 'quantity')
            ->orderByDesc('is_active')
            ->orderBy('created_at')
            ->get()
            ->each(function (RaffleDraw $draw) {
                $draw->total_tickets = (int) ($draw->total_tickets ?? 0);
            });

        $myTickets = [];

        if ($enrollment && $draws->isNotEmpty()) {
            $myTickets = RaffleTicket::query()
                ->where('enrollment_id', $enrollment->id)
                ->whereIn('raffle_draw_id', $draws->modelKeys())
                ->pluck('quantity', 'raffle_draw_id')
                ->all();
        }

        return Inertia::render('Raffle', [
            'draws' => $draws,
            'myTickets' => $myTickets,
            'points' => $enrollment?->points,
            'isEnrolled' => $enrollment !== null,
        ]);
    }

    public function buyTickets(Request $request, RaffleDraw $draw): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $edition = $request->input('edition');
        abort_if($edition === null, 404, 'Edição não encontrada');

        $user = $request->user();
        abort_unless($user?->isParticipant(), 403, 'Apenas participantes podem comprar bilhetes');

        $enrollment = $user->usertype->enrollments()
            ->where('edition_id', $edition->id)
            ->first();

        abort_if($enrollment === null, 422, 'Não inscrito nesta edição');
        abort_if($draw->edition_id !== $edition->id, 404, 'Sorteio não encontrado');

        $quantity = $request->integer('quantity');

        $cost = DB::transaction(function () use ($draw, $edition, $enrollment, $quantity) {
            $lockedDraw = RaffleDraw::lockForUpdate()->findOrFail($draw->id);

            abort_if($lockedDraw->edition_id !== $edition->id, 404, 'Sorteio não encontrado');
            abort_if(! $lockedDraw->is_active, 422, 'Sorteio encerrado');
            abort_if($lockedDraw->winner_enrollment_id !== null, 422, 'Sorteio já realizado');
            abort_if($lockedDraw->draw_at !== null && $lockedDraw->draw_at->isPast(), 422, 'Prazo encerrado');

            $lockedEnrollment = Enrollment::lockForUpdate()->find($enrollment->id);
            abort_if($lockedEnrollment === null, 422, 'Não inscrito nesta edição');

            $cost = $lockedDraw->ticket_price * $quantity;
            abort_if($lockedEnrollment->points < $cost, 422, 'Saldo insuficiente');

            $lockedEnrollment->decrement('points', $cost);

            $ticket = RaffleTicket::query()
                ->where('raffle_draw_id', $lockedDraw->id)
                ->where('enrollment_id', $lockedEnrollment->id)
                ->lockForUpdate()
                ->first();

            if ($ticket !== null) {
                $ticket->increment('quantity', $quantity);
            } else {
                RaffleTicket::create([
                    'raffle_draw_id' => $lockedDraw->id,
                    'enrollment_id' => $lockedEnrollment->id,
                    'quantity' => $quantity,
                ]);
            }

            return $cost;
        });

        Log::info('Raffle tickets purchased', [
            'user_id' => $user->id,
            'draw_id' => $draw->id,
            'quantity' => $quantity,
            'cost' => $cost,
        ]);

        return redirect()->back()->banner('Bilhetes comprados com sucesso!');
    }
}
