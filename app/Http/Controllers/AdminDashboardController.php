<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Edition;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventDay;
use App\Models\Product;
use App\Models\Quest;
use App\Models\Sponsor;
use App\Models\Stand;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $currentEdition = Edition::orderByDesc('year')->first();

        $stats = [
            'users_count' => User::count(),
            'enrollments_count' => $currentEdition ? $currentEdition->enrollments()->count() : Enrollment::count(),
            'events_count' => $currentEdition ? $currentEdition->events()->count() : Event::count(),
            'event_days_count' => $currentEdition ? $currentEdition->event_days()->count() : EventDay::count(),
            'sponsors_count' => $currentEdition ? Sponsor::where('edition_id', $currentEdition->id)->count() : Sponsor::count(),
            'stands_count' => Stand::count(),
            'products_count' => Product::count(),
            'quests_count' => Quest::count(),
            'competitions_count' => $currentEdition ? Competition::where('edition_id', $currentEdition->id)->count() : Competition::count(),
        ];

        $upcomingEvents = Event::with(['event_day', 'type'])
            ->orderBy('event_day_id')
            ->orderBy('time_start')
            ->limit(5)
            ->get();

        $recentEnrollments = Enrollment::with(['participant.user', 'edition'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($enrollment) => [
                'id' => $enrollment->id,
                'name' => $enrollment->participant?->user?->name ?? 'Utilizador Desconhecido',
                'email' => $enrollment->participant?->user?->email ?? '-',
                'edition' => $enrollment->edition?->name ?? '-',
                'points' => $enrollment->points,
                'created_at' => $enrollment->created_at?->format('d/m/Y H:i') ?? '-',
            ]);

        return Inertia::render('Admin', [
            'currentEdition' => $currentEdition,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'recentEnrollments' => $recentEnrollments,
        ]);
    }
}
