<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $rewards = ReferralReward::query()->forUser($user->id)->with(['referral', 'order'])->orderBy('created_at', 'desc')->paginate(15);

        // Gather all reward statistics in a single query to avoid repeated database work.
        $stats = $this->buildStatsForUser($user->id);

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
    public function apiIndex(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = ReferralReward::forUser($user->id)->with(['referral', 'order'])->orderBy('created_at', 'desc')->get()->map(fn ($reward) => $reward->display_data);

        return response()->json(['success' => true, 'data' => $rewards]);
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
        // Reuse the shared statistics method for API consumers.
        $stats = $this->buildStatsForUser($user->id);

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Handle apiPending functionality with proper error handling.
     */
    public function apiPending(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = ReferralReward::forUser($user->id)->pending()->with(['referral', 'order'])->orderBy('created_at', 'desc')->get()->map(fn ($reward) => $reward->display_data);

        return response()->json(['success' => true, 'data' => $rewards]);
    }

    /**
     * Handle apiApplied functionality with proper error handling.
     */
    public function apiApplied(): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $rewards = ReferralReward::forUser($user->id)->applied()->with(['referral', 'order'])->orderBy('applied_at', 'desc')->get()->map(fn ($reward) => $reward->display_data);

        return response()->json(['success' => true, 'data' => $rewards]);
    }

    /**
     * Handle apiByType functionality with proper error handling.
     */
    public function apiByType(string $type): JsonResponse
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
        $rewards = $query->with(['referral', 'order'])->orderBy('created_at', 'desc')->get()->map(fn ($reward) => $reward->display_data);

        return response()->json(['success' => true, 'data' => $rewards]);
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
        $rewards = ReferralReward::forUser($user->id)->byDateRange($request->start_date, $request->end_date)->with(['referral', 'order'])->orderBy('created_at', 'desc')->get()->map(fn ($reward) => $reward->display_data);

        return response()->json(['success' => true, 'data' => $rewards]);
    }

    /**
     * Hydrate aggregated reward statistics for the authenticated user.
     */
    private function buildStatsForUser(int $userId): array
    {
        // Pull every metric with conditional sums/counts so dashboards execute just one query.
        $aggregates = ReferralReward::query()
            ->forUser($userId)
            ->selectRaw('COUNT(*) as total_rewards')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_rewards")
            ->selectRaw("SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as applied_rewards")
            ->selectRaw(
                "SUM(CASE WHEN status = 'expired' OR (expires_at IS NOT NULL AND expires_at <= ?) THEN 1 ELSE 0 END) as expired_rewards",
                [now()]
            )
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'applied' THEN amount ELSE 0 END), 0) as applied_amount")
            ->selectRaw("SUM(CASE WHEN type = 'referrer_bonus' THEN 1 ELSE 0 END) as referrer_bonuses")
            ->selectRaw("SUM(CASE WHEN type = 'referred_discount' THEN 1 ELSE 0 END) as referred_discounts")
            ->first();

        if ($aggregates === null) {
            // Safeguard against null responses by returning empty counters.
            return [
                'total_rewards' => 0,
                'pending_rewards' => 0,
                'applied_rewards' => 0,
                'expired_rewards' => 0,
                'total_amount' => 0.0,
                'pending_amount' => 0.0,
                'applied_amount' => 0.0,
                'referrer_bonuses' => 0,
                'referred_discounts' => 0,
            ];
        }

        // Convert database scalars into the expected array structure with strict typing.
        return [
            'total_rewards' => (int) ($aggregates->total_rewards ?? 0),
            'pending_rewards' => (int) ($aggregates->pending_rewards ?? 0),
            'applied_rewards' => (int) ($aggregates->applied_rewards ?? 0),
            'expired_rewards' => (int) ($aggregates->expired_rewards ?? 0),
            'total_amount' => (float) ($aggregates->total_amount ?? 0.0),
            'pending_amount' => (float) ($aggregates->pending_amount ?? 0.0),
            'applied_amount' => (float) ($aggregates->applied_amount ?? 0.0),
            'referrer_bonuses' => (int) ($aggregates->referrer_bonuses ?? 0),
            'referred_discounts' => (int) ($aggregates->referred_discounts ?? 0),
        ];
    }
}
