<?php

namespace App\Http\Controllers;

use App\Models\ArcadeNetFlowReview;
use App\Models\ArcadeSession;
use App\Models\Edition;
use App\Services\ArcadeNetFlowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ArcadeNetFlowController extends Controller
{
    public function index(Request $request, ArcadeNetFlowService $service): Response
    {
        /** @var Edition|null $edition */
        $edition = $request->input('edition');
        $editionFilter = $this->editionFilter($request, $edition);
        $allPairs = $service->pairs($editionFilter);

        $summary = [
            'pairs' => $allPairs->count(),
            'suspicious' => $allPairs->whereIn('risk_level', [
                ArcadeNetFlowService::RISK_HIGH,
                ArcadeNetFlowService::RISK_MEDIUM,
            ])->count(),
            'high_risk' => $allPairs->where('risk_level', ArcadeNetFlowService::RISK_HIGH)->count(),
            'net_flow' => (int) $allPairs->sum('net_flow'),
            'matches' => (int) $allPairs->sum('matches'),
            'unreviewed' => $allPairs->where('review_state', ArcadeNetFlowService::REVIEW_UNREVIEWED)->count(),
            'risk_counts' => [
                ArcadeNetFlowService::RISK_HIGH => $allPairs->where('risk_level', ArcadeNetFlowService::RISK_HIGH)->count(),
                ArcadeNetFlowService::RISK_MEDIUM => $allPairs->where('risk_level', ArcadeNetFlowService::RISK_MEDIUM)->count(),
                ArcadeNetFlowService::RISK_LOW => $allPairs->where('risk_level', ArcadeNetFlowService::RISK_LOW)->count(),
            ],
            'review_counts' => [
                ArcadeNetFlowService::REVIEW_UNREVIEWED => $allPairs->where('review_state', ArcadeNetFlowService::REVIEW_UNREVIEWED)->count(),
                ArcadeNetFlowReview::STATUS_CLEARED => $allPairs->where('review_state', ArcadeNetFlowReview::STATUS_CLEARED)->count(),
                ArcadeNetFlowReview::STATUS_FLAGGED => $allPairs->where('review_state', ArcadeNetFlowReview::STATUS_FLAGGED)->count(),
            ],
        ];

        $query = trim((string) $request->query('query', ''));
        $risk = (string) $request->query('risk', '');
        $reviewStatus = (string) $request->query('review_status', '');

        $pairs = $allPairs;

        if ($query !== '') {
            $needle = Str::lower($query);
            $pairs = $pairs->filter(function (array $pair) use ($needle) {
                $haystack = Str::lower(implode(' ', array_filter([
                    $pair['low']['name'],
                    $pair['low']['email'],
                    $pair['high']['name'],
                    $pair['high']['email'],
                ])));

                return Str::contains($haystack, $needle);
            })->values();
        }

        if (in_array($risk, [
            ArcadeNetFlowService::RISK_HIGH,
            ArcadeNetFlowService::RISK_MEDIUM,
            ArcadeNetFlowService::RISK_LOW,
        ], true)) {
            $pairs = $pairs->where('risk_level', $risk)->values();
        } else {
            $risk = 'all';
        }

        if (in_array($reviewStatus, [
            ArcadeNetFlowService::REVIEW_UNREVIEWED,
            ArcadeNetFlowReview::STATUS_CLEARED,
            ArcadeNetFlowReview::STATUS_FLAGGED,
        ], true)) {
            $pairs = $pairs->where('review_state', $reviewStatus)->values();
        } else {
            $reviewStatus = 'all';
        }

        $sort = (string) $request->query('sort', 'net_flow');
        if (! in_array($sort, ['net_flow', 'matches', 'dominant_share', 'last_match_at'], true)) {
            $sort = 'net_flow';
        }

        $direction = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        $pairs = $pairs->sortBy([
            [$sort, $direction],
            ['net_flow', 'desc'],
            ['matches', 'desc'],
        ])->values();

        $perPage = 25;
        $total = $pairs->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $items = $pairs->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return Inertia::render('Admin/ArcadeNetFlow', [
            'pairs' => $paginator,
            'summary' => $summary,
            'filters' => [
                'edition_id' => $editionFilter === null ? 'all' : (string) $editionFilter,
                'query' => $query,
                'risk' => $risk,
                'review_status' => $reviewStatus,
                'sort' => $sort,
                'direction' => $direction,
            ],
            'editions' => Edition::query()
                ->select('id', 'name', 'year')
                ->orderByDesc('year')
                ->get(),
        ]);
    }

    public function review(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'low_enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'high_enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'status' => [
                'required',
                Rule::in([
                    ArcadeNetFlowReview::STATUS_OPEN,
                    ArcadeNetFlowReview::STATUS_CLEARED,
                    ArcadeNetFlowReview::STATUS_FLAGGED,
                ]),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $lowEnrollmentId = (int) $validated['low_enrollment_id'];
        $highEnrollmentId = (int) $validated['high_enrollment_id'];

        if ($lowEnrollmentId >= $highEnrollmentId) {
            throw ValidationException::withMessages([
                'high_enrollment_id' => 'Par de contas inválido.',
            ]);
        }

        $matches = $this->pairMatchCount($lowEnrollmentId, $highEnrollmentId);

        if ($matches === 0) {
            throw ValidationException::withMessages([
                'low_enrollment_id' => 'Não existem partidas resolvidas entre estas contas.',
            ]);
        }

        $status = $validated['status'];

        ArcadeNetFlowReview::updateOrCreate(
            [
                'low_enrollment_id' => $lowEnrollmentId,
                'high_enrollment_id' => $highEnrollmentId,
            ],
            [
                'status' => $status,
                'note' => $validated['note'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_match_count' => $status === ArcadeNetFlowReview::STATUS_OPEN ? 0 : $matches,
                'reviewed_at' => now(),
            ],
        );

        Log::info('Arcade net flow pair reviewed', [
            'low_enrollment_id' => $lowEnrollmentId,
            'high_enrollment_id' => $highEnrollmentId,
            'status' => $status,
            'matches' => $matches,
            'admin_id' => $request->user()->id,
        ]);

        return redirect()->back()->banner(match ($status) {
            ArcadeNetFlowReview::STATUS_CLEARED => 'Par marcado como revisto.',
            ArcadeNetFlowReview::STATUS_FLAGGED => 'Par sinalizado para revisão.',
            default => 'Revisão reaberta.',
        });
    }

    private function editionFilter(Request $request, ?Edition $edition): ?int
    {
        $requested = $request->query('edition_id');

        if ($requested === 'all') {
            return null;
        }

        if (is_int($requested) || (is_string($requested) && ctype_digit($requested))) {
            return (int) $requested;
        }

        return $edition?->id;
    }

    private function pairMatchCount(int $lowEnrollmentId, int $highEnrollmentId): int
    {
        return ArcadeSession::query()
            ->whereHas('game', fn ($query) => $query->where('is_pvp', true))
            ->whereNotNull('challenger_enrollment_id')
            ->whereIn('outcome', ['win', 'loss'])
            ->whereNotNull('resolved_at')
            ->where(function ($query) use ($lowEnrollmentId, $highEnrollmentId) {
                $query->where(function ($query) use ($lowEnrollmentId, $highEnrollmentId) {
                    $query->where('enrollment_id', $lowEnrollmentId)
                        ->where('challenger_enrollment_id', $highEnrollmentId);
                })->orWhere(function ($query) use ($lowEnrollmentId, $highEnrollmentId) {
                    $query->where('enrollment_id', $highEnrollmentId)
                        ->where('challenger_enrollment_id', $lowEnrollmentId);
                });
            })
            ->count();
    }
}
