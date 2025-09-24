<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DiscountRedemption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DiscountRedemptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $user
            ->discountRedemptions()
            ->with(['discount', 'code', 'order'])
            ->latest('redeemed_at');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('currency')) {
            $query->where('currency_code', $request->currency);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('redeemed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('redeemed_at', '<=', $request->date_to);
        }

        $redemptions = $query->paginate(15);

        // Calculate stats
        $totalRedemptions = $user->discountRedemptions()->count();
        $totalSaved = $user
            ->discountRedemptions()
            ->where('status', 'redeemed')
            ->sum('amount_saved');
        $pendingRedemptions = $user
            ->discountRedemptions()
            ->where('status', 'pending')
            ->count();
        $redeemedRedemptions = $user
            ->discountRedemptions()
            ->where('status', 'redeemed')
            ->count();

        return view('frontend.discount-redemptions.index', compact(
            'redemptions',
            'totalRedemptions',
            'totalSaved',
            'pendingRedemptions',
            'redeemedRedemptions'
        ));
    }

    public function show(DiscountRedemption $redemption): View
    {
        // Ensure user can only view their own redemptions
        if ($redemption->user_id !== auth()->id()) {
            abort(403);
        }

        $redemption->load(['discount', 'code', 'order']);

        return view('frontend.discount-redemptions.show', compact('redemption'));
    }

    public function create(): View
    {
        return view('frontend.discount-redemptions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'discount_code' => 'required|string|exists:discount_codes,code',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $discountCode = \App\Models\DiscountCode::where('code', $request->discount_code)->first();

        if (! $discountCode) {
            return back()->withErrors(['discount_code' => __('Invalid discount code.')]);
        }

        // Check if code is active and not expired
        if (! $discountCode->is_active || $discountCode->expires_at < now()) {
            return back()->withErrors(['discount_code' => __('This discount code is no longer valid.')]);
        }

        // Check usage limits
        if ($discountCode->usage_limit && $discountCode->usage_count >= $discountCode->usage_limit) {
            return back()->withErrors(['discount_code' => __('This discount code has reached its usage limit.')]);
        }

        // Check per-user usage limit
        $userRedemptions = auth()
            ->user()
            ->discountRedemptions()
            ->where('code_id', $discountCode->id)
            ->count();

        if ($discountCode->usage_limit_per_user && $userRedemptions >= $discountCode->usage_limit_per_user) {
            return back()->withErrors(['discount_code' => __('You have already used this discount code the maximum number of times.')]);
        }

        // Create redemption
        $redemption = DiscountRedemption::create([
            'discount_id' => $discountCode->discount_id,
            'code_id' => $discountCode->id,
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'amount_saved' => $discountCode->discount->value,
            'currency_code' => 'EUR',
            'redeemed_at' => now(),
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Update usage count
        $discountCode->increment('usage_count');

        return redirect()
            ->route('frontend.discount-redemptions.show', $redemption)
            ->with('success', __('Discount code redeemed successfully!'));
    }
}
