<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final /**
 * ReferralController
 * 
 * HTTP controller handling web requests and responses.
 */
class ReferralController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with(['referred', 'rewards'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $completedReferrals = Referral::where('referrer_id', $user->id)->completed()->count();
        $totalRewards = ReferralReward::where('user_id', $user->id)->sum('amount');
        $pendingRewards = ReferralReward::where('user_id', $user->id)->pending()->sum('amount');

        return view('referrals.index', compact(
            'referrals',
            'totalReferrals',
            'completedReferrals',
            'totalRewards',
            'pendingRewards'
        ));
    }

    public function show(string $code): View
    {
        $referral = Referral::where('referral_code', $code)->firstOrFail();

        return view('referrals.show', compact('referral'));
    }

    public function create(): View
    {
        $user = Auth::user();

        // Check if user can create referral
        if (! Referral::canUserRefer($user->id)) {
            return redirect()->route('referrals.index')
                ->with('error', __('referrals.referral_limit_reached'));
        }

        return view('referrals.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'referred_email' => 'required|email|exists:users,email',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $referredUser = User::where('email', $request->referred_email)->first();

        // Check if user is trying to refer themselves
        if ($referredUser->id === $user->id) {
            return redirect()->back()
                ->with('error', __('referrals.cannot_refer_yourself'));
        }

        // Check if user has already been referred
        if (Referral::userAlreadyReferred($referredUser->id)) {
            return redirect()->back()
                ->with('error', __('referrals.user_already_referred'));
        }

        // Check if user can refer
        if (! Referral::canUserRefer($user->id)) {
            return redirect()->back()
                ->with('error', __('referrals.referral_limit_reached'));
        }

        try {
            DB::beginTransaction();

            $referral = Referral::createWithCode([
                'referrer_id' => $user->id,
                'referred_id' => $referredUser->id,
                'status' => 'pending',
                'title' => [
                    'en' => $request->title ?? __('referrals.default_title'),
                    'lt' => $request->title ?? __('referrals.default_title'),
                ],
                'description' => [
                    'en' => $request->description ?? __('referrals.default_description'),
                    'lt' => $request->description ?? __('referrals.default_description'),
                ],
                'source' => 'website',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('referrals.index')
                ->with('success', __('referrals.referral_created'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('referrals.referral_creation_failed'));
        }
    }

    public function generateCode(): JsonResponse
    {
        $user = Auth::user();

        // Check if user already has an active referral code
        if ($user->referral_code) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.code_already_exists'),
                'code' => $user->referral_code,
            ]);
        }

        try {
            $code = Referral::generateUniqueCode();

            $user->update([
                'referral_code' => $code,
                'referral_code_generated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => __('referrals.code_generated'),
                'code' => $code,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.code_generation_failed'),
            ]);
        }
    }

    public function applyCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $user = Auth::user();

        // Check if user has already been referred
        if (Referral::userAlreadyReferred($user->id)) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.user_already_referred'),
            ]);
        }

        // Find referral by code
        $referral = Referral::findByCode($request->code);

        if (! $referral) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.invalid_code'),
            ]);
        }

        // Check if referral is valid
        if (! $referral->isValid()) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.invalid_code'),
            ]);
        }

        // Check if user is trying to use their own code
        if ($referral->referrer_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('referrals.cannot_use_own_code'),
            ]);
        }

        try {
            DB::beginTransaction();

            // Update the referral to use this user
            $referral->update([
                'referred_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('referrals.code_applied'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => __('referrals.code_application_failed'),
            ]);
        }
    }

    public function shareCode(): View
    {
        $user = Auth::user();

        if (! $user->referral_code) {
            return redirect()->route('referrals.create')
                ->with('info', __('referrals.no_active_code'));
        }

        $shareText = __('referrals.share_text', [
            'code' => $user->referral_code,
            'url' => route('referrals.apply', $user->referral_code),
        ]);

        return view('referrals.share', compact('user', 'shareText'));
    }

    public function statistics(): View
    {
        $user = Auth::user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with(['referred', 'rewards'])
            ->get();

        $totalReferrals = $referrals->count();
        $completedReferrals = $referrals->where('status', 'completed')->count();
        $pendingReferrals = $referrals->where('status', 'pending')->count();
        $expiredReferrals = $referrals->where('status', 'expired')->count();

        $totalRewards = ReferralReward::where('user_id', $user->id)->sum('amount');
        $pendingRewards = ReferralReward::where('user_id', $user->id)->pending()->sum('amount');
        $appliedRewards = ReferralReward::where('user_id', $user->id)->applied()->sum('amount');

        $conversionRate = $totalReferrals > 0 ? round(($completedReferrals / $totalReferrals) * 100, 1) : 0;

        // Monthly statistics
        $monthlyStats = Referral::where('referrer_id', $user->id)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('referrals.statistics', compact(
            'totalReferrals',
            'completedReferrals',
            'pendingReferrals',
            'expiredReferrals',
            'totalRewards',
            'pendingRewards',
            'appliedRewards',
            'conversionRate',
            'monthlyStats'
        ));
    }

    public function rewards(): View
    {
        $user = Auth::user();

        $rewards = ReferralReward::where('user_id', $user->id)
            ->with(['referral.referred'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $totalRewards = ReferralReward::where('user_id', $user->id)->sum('amount');
        $pendingRewards = ReferralReward::where('user_id', $user->id)->pending()->sum('amount');
        $appliedRewards = ReferralReward::where('user_id', $user->id)->applied()->sum('amount');

        return view('referrals.rewards', compact(
            'rewards',
            'totalRewards',
            'pendingRewards',
            'appliedRewards'
        ));
    }
}
