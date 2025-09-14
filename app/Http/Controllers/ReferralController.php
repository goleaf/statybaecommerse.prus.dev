<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
/**
 * ReferralController
 * 
 * HTTP controller handling ReferralController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class ReferralController extends Controller
{
    use AuthorizesRequests;
    /**
     * Get referral code statistics
     * @return \Illuminate\Http\JsonResponse
     */
    public function codeStatistics(): \Illuminate\Http\JsonResponse
    {
        $stats = [
            'total_codes' => ReferralCode::count(),
            'active_codes' => ReferralCode::where('is_active', true)->count(),
            'total_usage' => ReferralCode::sum('usage_count'),
            'total_rewards' => ReferralCode::sum('total_rewards'),
        ];
        
        return response()->json($stats);
    }

    /**
     * Get referral URL
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReferralUrl(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['url' => null]);
        }
        
        $referralCode = $user->activeReferralCode();
        
        if ($referralCode) {
            $url = route('referrals.track', ['code' => $referralCode->code]);
        } else {
            $url = null;
        }
        
        return response()->json(['url' => $url]);
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        $referrals = $user->referrals()->with(['referred', 'rewards'])->latest()->get()->skipWhile(function ($referral) {
            // Skip referrals that are not properly configured for display
            return empty($referral->referred) || empty($referral->referred_id) || empty($referral->referrer_id) || empty($referral->status);
        })->paginate(10);
        $stats = ['total_referrals' => $user->referrals()->count(), 'completed_referrals' => $user->referrals()->completed()->count(), 'pending_referrals' => $user->referrals()->where('status', 'pending')->count(), 'total_rewards' => $user->referralRewards()->sum('amount')];
        $referralCode = $user->activeReferralCode();
        return view('referrals.index', compact('referrals', 'stats', 'referralCode'));
    }
    /**
     * Show the form for creating a new resource.
     * @return View
     */
    public function create(): View
    {
        $user = Auth::user();
        if (!Referral::canUserRefer($user->id)) {
            return redirect()->route('referrals.index')->with('error', __('referrals.referral_limit_reached'));
        }
        $referralCode = $user->activeReferralCode();
        return view('referrals.create', compact('referralCode'));
    }
    /**
     * Store a newly created resource in storage with validation.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!Referral::canUserRefer($user->id)) {
            return redirect()->route('referrals.index')->with('error', __('referrals.referral_limit_reached'));
        }
        $request->validate(['referred_email' => 'required|email|exists:users,email', 'message' => 'nullable|string|max:500']);
        $referredUser = User::where('email', $request->referred_email)->first();
        if ($referredUser->id === $user->id) {
            return redirect()->back()->with('error', __('referrals.cannot_refer_yourself'));
        }
        if (Referral::userAlreadyReferred($referredUser->id)) {
            return redirect()->back()->with('error', __('referrals.user_already_referred'));
        }
        $referral = Referral::createWithCode(['referrer_id' => $user->id, 'referred_id' => $referredUser->id, 'source' => $request->get('source', 'manual'), 'campaign' => $request->get('campaign'), 'utm_source' => $request->get('utm_source'), 'utm_medium' => $request->get('utm_medium'), 'utm_campaign' => $request->get('utm_campaign'), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'metadata' => ['message' => $request->message, 'created_via' => 'manual']]);
        // Update statistics
        $this->updateReferralStatistics($user->id, $referral->created_at->toDateString());
        return redirect()->route('referrals.index')->with('success', __('referrals.referral_created'));
    }
    /**
     * Display the specified resource with related data.
     * @param Referral $referral
     * @return View
     */
    public function show(Referral $referral): View
    {
        $this->authorize('view', $referral);
        $referral->load(['referrer', 'referred', 'rewards', 'analyticsEvents']);
        return view('referrals.show', compact('referral'));
    }
    /**
     * Handle generateCode functionality with proper error handling.
     * @return RedirectResponse
     */
    public function generateCode(): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasActiveReferralCode()) {
            return redirect()->route('referrals.index')->with('info', __('referrals.code_already_exists'));
        }
        $code = ReferralCode::generateUniqueCode();
        ReferralCode::create(['user_id' => $user->id, 'code' => $code, 'is_active' => true]);
        // Update user's referral code
        $user->update(['referral_code' => $code, 'referral_code_generated_at' => now()]);
        return redirect()->route('referrals.index')->with('success', __('referrals.code_generated'));
    }
    /**
     * Handle share functionality with proper error handling.
     * @param Request $request
     * @return View
     */
    public function share(Request $request): View
    {
        $user = Auth::user();
        $referralCode = $user->activeReferralCode();
        if (!$referralCode) {
            return redirect()->route('referrals.index')->with('error', __('referrals.no_active_code'));
        }
        $shareUrl = $referralCode->referral_url;
        $shareText = __('referrals.share_text', ['code' => $referralCode->code, 'url' => $shareUrl]);
        return view('referrals.share', compact('referralCode', 'shareUrl', 'shareText'));
    }
    /**
     * Handle track functionality with proper error handling.
     * @param Request $request
     * @param string $code
     * @return RedirectResponse
     */
    public function track(Request $request, string $code): RedirectResponse
    {
        $referralCode = ReferralCode::findByCode($code);
        if (!$referralCode || !$referralCode->isValid()) {
            return redirect()->route('register')->with('error', __('referrals.invalid_code'));
        }
        // Store referral code in session for registration
        session(['referral_code' => $code]);
        // Track the click
        $this->trackReferralClick($referralCode, $request);
        return redirect()->route('register')->with('success', __('referrals.code_applied'));
    }
    /**
     * Handle rewards functionality with proper error handling.
     * @return View
     */
    public function rewards(): View
    {
        $user = Auth::user();
        $rewards = $user->referralRewards()->with(['referral.referred'])->latest()->paginate(10);
        $stats = ['total_rewards' => $user->referralRewards()->sum('amount'), 'pending_rewards' => $user->referralRewards()->pending()->sum('amount'), 'applied_rewards' => $user->referralRewards()->applied()->sum('amount')];
        return view('referrals.rewards', compact('rewards', 'stats'));
    }
    /**
     * Handle statistics functionality with proper error handling.
     * @return View
     */
    public function statistics(): View
    {
        $user = Auth::user();
        $stats = $user->referral_statistics;
        // Get monthly data for chart
        $monthlyData = DB::table('referral_statistics')->where('user_id', $user->id)->where('date', '>=', now()->subMonths(12))->orderBy('date')->get();
        return view('referrals.statistics', compact('stats', 'monthlyData'));
    }
    /**
     * Handle updateReferralStatistics functionality with proper error handling.
     * @param int $userId
     * @param string $date
     * @return void
     */
    private function updateReferralStatistics(int $userId, string $date): void
    {
        $stats = \App\Models\ReferralStatistics::getOrCreateForUserAndDate($userId, $date);
        $stats->incrementReferrals();
    }
    /**
     * Handle trackReferralClick functionality with proper error handling.
     * @param ReferralCode $referralCode
     * @param Request $request
     * @return void
     */
    private function trackReferralClick(ReferralCode $referralCode, Request $request): void
    {
        // Track analytics event
        \App\Models\AnalyticsEvent::create(['user_id' => $referralCode->user_id, 'event_type' => 'referral_click', 'session_id' => $request->session()->getId(), 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'referrer' => $request->header('referer'), 'properties' => ['referral_code' => $referralCode->code]]);
    }
}