<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

final class ReferralRewardController extends Controller
{
    /**
     * Display the user's referral rewards
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if (!$user) {
            abort(401);
        }

        $rewards = ReferralReward::forUser($user->id)
            ->with(['referral', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total_rewards' => ReferralReward::forUser($user->id)->count(),
            'pending_rewards' => ReferralReward::forUser($user->id)->pending()->count(),
            'applied_rewards' => ReferralReward::forUser($user->id)->applied()->count(),
            'expired_rewards' => ReferralReward::forUser($user->id)->expired()->count(),
            'total_amount' => ReferralReward::forUser($user->id)->sum('amount'),
            'pending_amount' => ReferralReward::forUser($user->id)->pending()->sum('amount'),
            'applied_amount' => ReferralReward::forUser($user->id)->applied()->sum('amount'),
        ];

        return view('frontend.referral-rewards.index', compact('rewards', 'stats'));
    }

    /**
     * Display a specific referral reward
     */
    public function show(ReferralReward $reward): View
    {
        $user = Auth::user();
        
        if (!$user || $reward->user_id !== $user->id) {
            abort(403);
        }

        $reward->load(['referral', 'order', 'logs']);

        return view('frontend.referral-rewards.show', compact('reward'));
    }

    /**
     * Get user's referral rewards as JSON
     */
    public function apiIndex(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rewards = ReferralReward::forUser($user->id)
            ->with(['referral', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($reward) => $reward->display_data);

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Get user's referral reward statistics
     */
    public function apiStats(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $stats = [
            'total_rewards' => ReferralReward::forUser($user->id)->count(),
            'pending_rewards' => ReferralReward::forUser($user->id)->pending()->count(),
            'applied_rewards' => ReferralReward::forUser($user->id)->applied()->count(),
            'expired_rewards' => ReferralReward::forUser($user->id)->expired()->count(),
            'total_amount' => ReferralReward::forUser($user->id)->sum('amount'),
            'pending_amount' => ReferralReward::forUser($user->id)->pending()->sum('amount'),
            'applied_amount' => ReferralReward::forUser($user->id)->applied()->sum('amount'),
            'referrer_bonuses' => ReferralReward::forUser($user->id)->referrerBonus()->count(),
            'referred_discounts' => ReferralReward::forUser($user->id)->referredDiscount()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get pending rewards for the user
     */
    public function apiPending(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rewards = ReferralReward::forUser($user->id)
            ->pending()
            ->with(['referral', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($reward) => $reward->display_data);

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Get applied rewards for the user
     */
    public function apiApplied(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $rewards = ReferralReward::forUser($user->id)
            ->applied()
            ->with(['referral', 'order'])
            ->orderBy('applied_at', 'desc')
            ->get()
            ->map(fn($reward) => $reward->display_data);

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Get rewards by type
     */
    public function apiByType(string $type): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
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

        $rewards = $query->with(['referral', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($reward) => $reward->display_data);

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }

    /**
     * Get rewards by date range
     */
    public function apiByDateRange(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $rewards = ReferralReward::forUser($user->id)
            ->byDateRange($request->start_date, $request->end_date)
            ->with(['referral', 'order'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($reward) => $reward->display_data);

        return response()->json([
            'success' => true,
            'data' => $rewards,
        ]);
    }
}
