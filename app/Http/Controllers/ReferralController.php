<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreReferralRequest;
use App\Models\Referral;
use App\Models\ReferralCode;
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
            return redirect()->route('referrals.index')->with('error', __('messages.referrals'));
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
            return redirect()->route('referrals.index')->with('error', __('messages.referrals'));
        }
        $validated = $request->validated();
        $referredUser = User::query()->where('email', $validated['referred_email'])->first();

        if (! $referredUser instanceof User) {
            // Surface a validation style error so the customer can correct the
            // email if the account was removed between validation and storage.
            return redirect()->back()->withErrors([
                'referred_email' => __('validation.exists', ['attribute' => __('messages.referrals')]),
            ])->withInput();
        }

        if ($referredUser->is($user)) {
            return redirect()->back()->with('error', __('messages.referrals'));
        }
        if (Referral::userAlreadyReferred($referredUser->id)) {
            return redirect()->back()->with('error', __('messages.referrals'));
        }

        // Normalise the optional marketing copy so both locales stay in sync.
        $title = $validated['title'] ?? __('referrals.create.title');
        $titleLt = $validated['title'] ?? __('referrals.create.title', [], 'lt');
        $description = $validated['description'] ?? __('referrals.create.description');
        $descriptionLt = $validated['description'] ?? __('referrals.create.description', [], 'lt');

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

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            report($exception);

            return redirect()->back()->with('error', __('messages.referrals'));
        }

        return redirect()->route('referrals.index')->with('success', __('messages.referrals'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Referral $referral): View
    {
        $this->authorize('view', $referral);
        $referral->load(['referrer', 'referred', 'rewards']);

        return view('referrals.show', compact('referral'));
    }

    /**
     * Handle generateCode functionality with proper error handling.
     */
    public function generateCode(): RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();
        if ($user->hasActiveReferralCode()) {
            return redirect()->route('referrals.index')->with('info', __('messages.referrals'));
        }
        $code = ReferralCode::generateUniqueCode();
        ReferralCode::create(['user_id' => $user->id, 'code' => $code, 'is_active' => true]);
        // Update user's referral code
        $user->update(['referral_code' => $code, 'referral_code_generated_at' => now()]);

        return redirect()->route('referrals.index')->with('success', __('messages.referrals'));
    }

    /**
     * Handle share functionality with proper error handling.
     */
    public function share(Request $request): View|RedirectResponse
    {
        $user = $this->resolveAuthenticatedUser();
        $referralCode = $user->activeReferralCode();
        if (! $referralCode) {
            return redirect()->route('referrals.create')->with('info', __('messages.referrals'));
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
            return redirect()->route('register')->with('error', __('messages.referrals'));
        }
        // Store referral code in session for registration
        session(['referral_code' => $code]);

        return redirect()->route('register')->with('success', __('messages.referrals'));
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
