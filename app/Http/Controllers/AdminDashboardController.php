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
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        $currentEdition = Edition::query()
            ->withCount(['enrollments', 'events', 'event_days', 'sponsors', 'competitions'])
            ->orderByDesc('year')
            ->first();

        $stats = [
            'users_count' => User::count(),
            'enrollments_count' => $currentEdition?->enrollments_count ?? Enrollment::count(),
            'events_count' => $currentEdition?->events_count ?? Event::count(),
            'event_days_count' => $currentEdition?->event_days_count ?? EventDay::count(),
            'sponsors_count' => $currentEdition?->sponsors_count ?? Sponsor::count(),
            'stands_count' => Stand::count(),
            'products_count' => Product::count(),
            'quests_count' => Quest::count(),
            'competitions_count' => $currentEdition?->competitions_count ?? Competition::count(),
        ];

        return Inertia::render('Admin', [
            'currentEdition' => $currentEdition,
            'stats' => $stats,
        ]);
    }
}
