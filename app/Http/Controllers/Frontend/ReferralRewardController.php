<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

/**
 * ReferralRewardController
 *
 * HTTP controller handling ReferralRewardController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ReferralRewardController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): View
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }
        $rewards = ReferralReward::forUser($user->id)->with(['referral', 'order'])->orderBy('created_at', 'desc')->paginate(15);
        $stats = ['total_rewards' => ReferralReward::forUser($user->id)->count(), 'pending_rewards' => ReferralReward::forUser($user->id)->pending()->count(), 'applied_rewards' => ReferralReward::forUser($user->id)->applied()->count(), 'expired_rewards' => ReferralReward::forUser($user->id)->expired()->count(), 'total_amount' => ReferralReward::forUser($user->id)->sum('amount'), 'pending_amount' => ReferralReward::forUser($user->id)->pending()->sum('amount'), 'applied_amount' => ReferralReward::forUser($user->id)->applied()->sum('amount')];

        return view('referral-rewards.index', compact('rewards', 'stats'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(ReferralReward $reward): View
    {
        $user = Auth::user();
        if (! $user || $reward->user_id !== $user->id) {
            abort(403);
        }
        $reward->load(['referral', 'order', 'logs']);

        return view('referral-rewards.show', compact('reward'));
    }

    /**
     * Handle apiIndex functionality with proper error handling.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = $this->paginateRewards($request, ReferralReward::forUser($user->id));

        return response()->json($this->buildPaginatorPayload($rewards));
    }

    /**
     * Handle apiStats functionality with proper error handling.
     */
    public function apiStats(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $stats = $this->aggregateStats($user->id);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Handle apiPending functionality with proper error handling.
     */
    public function apiPending(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = $this->paginateRewards($request, ReferralReward::forUser($user->id)->pending());

        return response()->json($this->buildPaginatorPayload($rewards));
    }

    /**
     * Handle apiApplied functionality with proper error handling.
     */
    public function apiApplied(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = $this->paginateRewards($request, ReferralReward::forUser($user->id)->applied(), 'applied_at');

        return response()->json($this->buildPaginatorPayload($rewards));
    }

    /**
     * Handle apiByType functionality with proper error handling.
     */
    public function apiByType(Request $request, string $type): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $query = ReferralReward::forUser($user->id);
        if ($type === 'referrer_bonus') {
            $query->referrerBonus();
        } elseif ($type === 'referred_discount') {
            $query->referredDiscount();
        } else {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        $rewards = $this->paginateRewards($request, $query);

        return response()->json($this->buildPaginatorPayload($rewards));
    }

    /**
     * Handle apiByDateRange functionality with proper error handling.
     */
    public function apiByDateRange(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date']);
        $rewards = $this->paginateRewards($request, ReferralReward::forUser($user->id)->byDateRange($request->start_date, $request->end_date));

        return response()->json($this->buildPaginatorPayload($rewards));
    }

    /**
     * Build aggregated statistics without loading the full reward collection into memory.
     */
    private function aggregateStats(int $userId): array
    {
        $now = now();

        $stats = ReferralReward::query()
            ->forUser($userId)
            ->selectRaw('COUNT(*) as total_rewards')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_rewards")
            ->selectRaw("SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as applied_rewards")
            ->selectRaw("SUM(CASE WHEN status = 'expired' OR (expires_at IS NOT NULL AND expires_at <= ?) THEN 1 ELSE 0 END) as expired_rewards", [$now])
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'applied' THEN amount ELSE 0 END), 0) as applied_amount")
            ->selectRaw("SUM(CASE WHEN type = 'referrer_bonus' THEN 1 ELSE 0 END) as referrer_bonuses")
            ->selectRaw("SUM(CASE WHEN type = 'referred_discount' THEN 1 ELSE 0 END) as referred_discounts")
            ->first();

        return [
            'total_rewards' => (int) ($stats?->total_rewards ?? 0),
            'pending_rewards' => (int) ($stats?->pending_rewards ?? 0),
            'applied_rewards' => (int) ($stats?->applied_rewards ?? 0),
            'expired_rewards' => (int) ($stats?->expired_rewards ?? 0),
            'total_amount' => (float) ($stats?->total_amount ?? 0),
            'pending_amount' => (float) ($stats?->pending_amount ?? 0),
            'applied_amount' => (float) ($stats?->applied_amount ?? 0),
            'referrer_bonuses' => (int) ($stats?->referrer_bonuses ?? 0),
            'referred_discounts' => (int) ($stats?->referred_discounts ?? 0),
        ];
    }

    /**
     * Paginate referral reward queries while transforming each item to the display data payload.
     */
    private function paginateRewards(Request $request, Builder $query, string $orderColumn = 'created_at', string $orderDirection = 'desc'): LengthAwarePaginator
    {
        $perPage = $this->resolvePerPage($request);

        return $query
            ->with(['referral', 'order'])
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($perPage)
            ->through(static fn (ReferralReward $reward) => $reward->display_data);
    }

    /**
     * Normalise the pagination meta block for API consumers.
     */
    private function buildPaginatorPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ],
        ];
    }

    /**
     * Safely resolve the requested per-page value while preventing unbounded page sizes.
     */
    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->input('per_page', 25);

        // Clamp the requested page size to defend against abusive values that could exhaust memory.
        return max(1, min($perPage, 100));
    }
}
