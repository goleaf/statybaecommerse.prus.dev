<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReferralRequest;
use App\Models\AnalyticsEvent;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\ReferralStatistics;
use App\Models\User;
use App\Services\PaginationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/**
 * ReferralController
 *
 * HTTP controller handling ReferralController related web requests, responses, and business logic with proper validation and error handling.
 */
final class ReferralController extends Controller
{
    use AuthorizesRequests;

    /**
     * Handle codeStatistics functionality with proper error handling.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function codeStatistics(): JsonResponse
    {
        // Aggregate once to avoid multiple trips to the database when the
        // dashboard polls this endpoint for analytics tiles.
        $stats = [
            'total_codes'   => ReferralCode::count(),
            'active_codes'  => ReferralCode::where('is_active', true)->count(),
            'total_usage'   => (int) ReferralCode::sum('usage_count'),
            'total_rewards' => (float) ReferralCode::sum('total_rewards'),
        ];

        return response()->json($stats);
    }

    /**
     * Handle getReferralUrl functionality with proper error handling.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getReferralUrl(): JsonResponse
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return response()->json(['url' => null]);
        }
        $referralCode = $user->activeReferralCode();
        $url = $referralCode ? route('referrals.track', ['code' => $referralCode->code]) : null;

        return response()->json(['url' => $url]);
    }

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): View
    {
        $user = $this->resolveAuthenticatedUser();

        $referralsQuery = Referral::query()
            ->with(['referred', 'rewards'])
            ->where('referrer_id', $user->id)
            ->whereHas('referred')
            ->whereNotNull('referrals.referred_id')
            ->whereNotNull('referrals.referrer_id')
            ->whereNotNull('referrals.status')
            ->latest();

        $referrals = PaginationService::paginateQueryWithSkipWhile(
            clone $referralsQuery,
            static function (Referral $referral): bool {
                // Skip any leading referrals missing critical associations before paginating.
                return $referral->referred === null
                    || $referral->referrer_id === null
                    || empty($referral->status);
            },
            10
        );
        // Collapse referral counters into a single query to reduce load on the
        // referrals table when the list is refreshed frequently.
        $referralCounters = $user->referrals()
            ->selectRaw(
                'COUNT(*) as total,' .
                "COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed," .
                "COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending"
            )
            ->first();

        $stats = [
            'total_referrals'     => (int) ($referralCounters->total ?? 0),
            'completed_referrals' => (int) ($referralCounters->completed ?? 0),
            'pending_referrals'   => (int) ($referralCounters->pending ?? 0),
            'total_rewards'       => (float) $user->referralRewards()->sum('amount'),
            'pending_rewards'     => (float) $user->referralRewards()->pending()->sum('amount'),
        ];
        $referralCode = $user->activeReferralCode();

        return view('referrals.index', [
            'referrals'          => $referrals,
            'stats'              => $stats,
            'referralCode'       => $referralCode,
            'totalReferrals'     => $stats['total_referrals'],
            'completedReferrals' => $stats['completed_referrals'],
            'pendingReferrals'   => $stats['pending_referrals'],
            'totalRewards'       => $stats['total_rewards'],
            'pendingRewards'     => $stats['pending_rewards'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();

        if (! Referral::canUserRefer($user->id)) {
            return redirect()->route('referrals.index')->with('error', __('referrals.referral_limit_reached'));
        }
        $referralCode = $user->activeReferralCode();

        return view('referrals.create', compact('referralCode'));
    }

    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(StoreReferralRequest $request): RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();

        if (! Referral::canUserRefer($user->id)) {
            return redirect()->route('referrals.index')->with('error', __('referrals.referral_limit_reached'));
        }
        $validated = $request->validated();
        $referredUser = User::query()->where('email', $validated['referred_email'])->first();

        if (! $referredUser instanceof User) {
            // Surface a validation style error so the customer can correct the
            // email if the account was removed between validation and storage.
            return redirect()->back()->withErrors([
                'referred_email' => __('validation.exists', ['attribute' => __('referrals.email')]),
            ])->withInput();
        }

        if ($referredUser->is($user)) {
            return redirect()->back()->with('error', __('referrals.cannot_refer_yourself'));
        }
        if (Referral::userAlreadyReferred($referredUser->id)) {
            return redirect()->back()->with('error', __('referrals.user_already_referred'));
        }

        // Normalise the optional marketing copy so both locales stay in sync.
        $title = $validated['title'] ?? __('referrals.default_title');
        $titleLt = $validated['title'] ?? __('referrals.default_title', [], 'lt');
        $description = $validated['description'] ?? __('referrals.default_description');
        $descriptionLt = $validated['description'] ?? __('referrals.default_description', [], 'lt');

        try {
            DB::beginTransaction();

            $referral = Referral::createWithCode([
                'referrer_id'  => $user->id,
                'referred_id'  => $referredUser->id,
                'source'       => $request->get('source', 'website'),
                'campaign'     => $request->get('campaign'),
                'utm_source'   => $request->get('utm_source'),
                'utm_medium'   => $request->get('utm_medium'),
                'utm_campaign' => $request->get('utm_campaign'),
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'title'        => ['en' => $title, 'lt' => $titleLt],
                'description'  => ['en' => $description, 'lt' => $descriptionLt],
                'metadata'     => [
                    'message'     => $validated['message'] ?? null,
                    'created_via' => 'manual',
                ],
            ]);

            // Update statistics inside the same transaction to keep analytics
            // counters consistent with the referral record itself.
            $this->updateReferralStatistics($user->id, $referral->created_at->toDateString());

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return redirect()->back()->with('error', __('referrals.referral_creation_failed'));
        }

        return redirect()->route('referrals.index')->with('success', __('referrals.referral_created'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Referral $referral): View
    {
        $this->authorize('view', $referral);
        $referral->load(['referrer', 'referred', 'rewards', 'analyticsEvents']);

        return view('referrals.show', compact('referral'));
    }

    /**
     * Handle generateCode functionality with proper error handling.
     */
    public function generateCode(): RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();
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
     */
    public function share(Request $request): View|RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();
        $referralCode = $user->activeReferralCode();
        if (! $referralCode) {
            return redirect()->route('referrals.create')->with('info', __('referrals.no_active_code'));
        }
        $shareUrl = $referralCode->referral_url;
        $shareText = __('referrals.share_text', ['code' => $referralCode->code, 'url' => $shareUrl]);

        return view('referrals.share', [
            'user'         => $user,
            'referralCode' => $referralCode,
            'shareUrl'     => $shareUrl,
            'shareText'    => $shareText,
        ]);
    }

    /**
     * Handle track functionality with proper error handling.
     */
    public function track(Request $request, string $code): RedirectResponse
    {
        $referralCode = ReferralCode::findByCode($code);
        if (! $referralCode || ! $referralCode->isValid()) {
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
     */
    public function rewards(): View
    {
        $user = $this->resolveAuthenticatedUser();
        $rewards = $user->referralRewards()->with(['referral.referred'])->latest()->paginate(10);
        $stats = ['total_rewards' => $user->referralRewards()->sum('amount'), 'pending_rewards' => $user->referralRewards()->pending()->sum('amount'), 'applied_rewards' => $user->referralRewards()->applied()->sum('amount')];

        return view('referrals.rewards', compact('rewards', 'stats'));
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(): View
    {
        $user = $this->resolveAuthenticatedUser();
        $stats = $user->referral_statistics;
        // Get monthly data for chart
        $monthlyData = DB::table('referral_statistics')->where('user_id', $user->id)->where('date', '>=', now()->subMonths(12))->orderBy('date')->get()->skipWhile(function ($stat) {
            // Skip statistics that are not properly configured for display
            return empty($stat->date) || empty($stat->user_id) || $stat->referrals_count < 0 || $stat->rewards_amount < 0;
        });

        return view('referrals.statistics', compact('stats', 'monthlyData'));
    }

    /**
     * Handle updateReferralStatistics functionality with proper error handling.
     */
    private function updateReferralStatistics(int $userId, string $date): void
    {
        $stats = ReferralStatistics::getOrCreateForUserAndDate($userId, $date);
        $stats->incrementReferrals();
    }

    /**
     * Handle trackReferralClick functionality with proper error handling.
     */
    private function trackReferralClick(ReferralCode $referralCode, Request $request): void
    {
        // Track analytics event
        AnalyticsEvent::create([
            'user_id'    => $referralCode->user_id,
            'event_type' => 'referral_click',
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer'   => $request->header('referer'),
            'properties' => ['referral_code' => $referralCode->code],
        ]);
    }

    /**
     * Resolve the authenticated user or abort when the session is missing.
     */
    private function resolveAuthenticatedUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            // Guard against routes being reachable without the auth middleware.
            abort(403);
        }

        return $user;
    }
}
