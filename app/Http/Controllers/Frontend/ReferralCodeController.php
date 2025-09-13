<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ReferralCodeController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $referralCodes = $user->referralCodes()
            ->with(['campaign', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_codes' => $user->referralCodes()->count(),
            'active_codes' => $user->referralCodes()->active()->count(),
            'total_usage' => $user->referralCodes()->sum('usage_count'),
            'total_rewards' => $user->referralCodes()->sum('reward_amount'),
        ];

        return view('referral-codes.index', compact('referralCodes', 'stats'));
    }

    public function show(ReferralCode $referralCode): View
    {
        $this->authorize('view', $referralCode);

        $referralCode->load(['campaign', 'user', 'referrals', 'rewards', 'usageLogs', 'statistics']);

        $stats = $referralCode->stats;

        return view('referral-codes.show', compact('referralCode', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', ReferralCode::class);

        $campaigns = \App\Models\ReferralCampaign::active()->get();

        return view('referral-codes.create', compact('campaigns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ReferralCode::class);

        $validated = $request->validate([
            'title' => 'required|array',
            'title.lt' => 'required|string|max:255',
            'title.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.lt' => 'nullable|string',
            'description.en' => 'nullable|string',
            'usage_limit' => 'nullable|integer|min:1',
            'reward_amount' => 'nullable|numeric|min:0',
            'reward_type' => 'nullable|in:percentage,fixed,points',
            'campaign_id' => 'nullable|exists:referral_campaigns,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['code'] = ReferralCode::generateUniqueCode();
        $validated['is_active'] = true;
        $validated['source'] = 'user';
        $validated['usage_count'] = 0;

        $referralCode = ReferralCode::create($validated);

        return redirect()
            ->route('frontend.referral-codes.show', $referralCode)
            ->with('success', __('referral_codes.messages.created_successfully'));
    }

    public function edit(ReferralCode $referralCode): View
    {
        $this->authorize('update', $referralCode);

        $campaigns = \App\Models\ReferralCampaign::active()->get();

        return view('referral-codes.edit', compact('referralCode', 'campaigns'));
    }

    public function update(Request $request, ReferralCode $referralCode): RedirectResponse
    {
        $this->authorize('update', $referralCode);

        $validated = $request->validate([
            'title' => 'required|array',
            'title.lt' => 'required|string|max:255',
            'title.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.lt' => 'nullable|string',
            'description.en' => 'nullable|string',
            'usage_limit' => 'nullable|integer|min:1',
            'reward_amount' => 'nullable|numeric|min:0',
            'reward_type' => 'nullable|in:percentage,fixed,points',
            'campaign_id' => 'nullable|exists:referral_campaigns,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $referralCode->update($validated);

        return redirect()
            ->route('frontend.referral-codes.show', $referralCode)
            ->with('success', __('referral_codes.messages.updated_successfully'));
    }

    public function destroy(ReferralCode $referralCode): RedirectResponse
    {
        $this->authorize('delete', $referralCode);

        $referralCode->delete();

        return redirect()
            ->route('frontend.referral-codes.index')
            ->with('success', __('referral_codes.messages.deleted_successfully'));
    }

    public function toggle(ReferralCode $referralCode): JsonResponse
    {
        $this->authorize('update', $referralCode);

        $referralCode->update(['is_active' => ! $referralCode->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $referralCode->is_active,
            'message' => $referralCode->is_active
                ? __('referral_codes.messages.activated_successfully')
                : __('referral_codes.messages.deactivated_successfully'),
        ]);
    }

    public function copyUrl(ReferralCode $referralCode): JsonResponse
    {
        $this->authorize('view', $referralCode);

        return response()->json([
            'success' => true,
            'url' => $referralCode->referral_url,
            'message' => __('referral_codes.messages.url_copied'),
        ]);
    }

    public function stats(ReferralCode $referralCode): JsonResponse
    {
        $this->authorize('view', $referralCode);

        $stats = $referralCode->stats;

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    public function usage(ReferralCode $referralCode): View
    {
        $this->authorize('view', $referralCode);

        $usageLogs = $referralCode->usageLogs()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('referral-codes.usage', compact('referralCode', 'usageLogs'));
    }

    public function statistics(ReferralCode $referralCode): View
    {
        $this->authorize('view', $referralCode);

        $statistics = $referralCode->statistics()
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $chartData = $statistics->map(function ($stat) {
            return [
                'date' => $stat->date->format('Y-m-d'),
                'views' => $stat->total_views,
                'clicks' => $stat->total_clicks,
                'signups' => $stat->total_signups,
                'conversions' => $stat->total_conversions,
                'revenue' => $stat->total_revenue,
            ];
        });

        return view('referral-codes.statistics', compact('referralCode', 'statistics', 'chartData'));
    }
}
