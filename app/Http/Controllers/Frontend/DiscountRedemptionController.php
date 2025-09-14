<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final /**
 * DiscountRedemptionController
 * 
 * HTTP controller handling web requests and responses.
 */
class DiscountRedemptionController extends Controller
{
    /**
     * Display a listing of the user's discount redemptions
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = DiscountRedemption::with(['discount', 'code', 'order'])
            ->where('user_id', $user->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('discount_id')) {
            $query->where('discount_id', $request->discount_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('redeemed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('redeemed_at', '<=', $request->date_to);
        }

        $redemptions = $query->orderBy('redeemed_at', 'desc')->get()
            ->skipWhile(function ($redemption) {
                // Skip redemptions that are not properly configured for display
                return empty($redemption->discount_id) || 
                       empty($redemption->code_id) ||
                       empty($redemption->user_id) ||
                       empty($redemption->amount_saved) ||
                       empty($redemption->currency_code);
            })
            ->paginate(15);

        // Get user's available discounts for filter
        $availableDiscounts = Discount::where('is_active', true)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->get();

        $stats = [
            'total_redemptions' => DiscountRedemption::where('user_id', $user->id)->count(),
            'total_saved' => DiscountRedemption::where('user_id', $user->id)->sum('amount_saved'),
            'average_saved' => DiscountRedemption::where('user_id', $user->id)->avg('amount_saved') ?? 0,
            'this_month_redemptions' => DiscountRedemption::where('user_id', $user->id)
                ->whereMonth('redeemed_at', now()->month)
                ->whereYear('redeemed_at', now()->year)
                ->count(),
        ];

        return view('discount-redemptions.index', compact(
            'redemptions',
            'availableDiscounts',
            'stats'
        ));
    }

    /**
     * Display the specified discount redemption
     */
    public function show(DiscountRedemption $discountRedemption): View
    {
        $this->authorize('view', $discountRedemption);

        $discountRedemption->load(['discount', 'code', 'order', 'user']);

        return view('discount-redemptions.show', compact('discountRedemption'));
    }

    /**
     * Show the form for creating a new discount redemption
     */
    public function create(): View
    {
        $user = Auth::user();

        $availableDiscounts = Discount::where('is_active', true)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->with(['codes' => function ($query) {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                            ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            }])
            ->get();

        $userOrders = Order::where('user_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('discount-redemptions.create', compact(
            'availableDiscounts',
            'userOrders'
        ));
    }

    /**
     * Store a newly created discount redemption
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'discount_id' => 'required|exists:discounts,id',
            'code_id' => 'required|exists:discount_codes,id',
            'order_id' => 'nullable|exists:orders,id',
            'amount_saved' => 'required|numeric|min:0.01',
            'currency_code' => 'required|string|in:EUR,USD,GBP',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = Auth::user();

        // Check if discount is valid and available
        $discount = Discount::findOrFail($request->discount_id);
        if (! $discount->isValid()) {
            return redirect()->back()
                ->with('error', __('frontend.discount_redemptions.errors.discount_not_valid'))
                ->withInput();
        }

        // Check if code is valid and available
        $code = DiscountCode::findOrFail($request->code_id);
        if (! $code->is_active || $code->discount_id !== $discount->id) {
            return redirect()->back()
                ->with('error', __('frontend.discount_redemptions.errors.code_not_valid'))
                ->withInput();
        }

        // Check if code has reached usage limit
        if ($code->usage_limit && $code->usage_count >= $code->usage_limit) {
            return redirect()->back()
                ->with('error', __('frontend.discount_redemptions.errors.code_limit_reached'))
                ->withInput();
        }

        // Check if user has reached per-user limit for this code
        if ($code->usage_limit_per_user) {
            $userUsageCount = DiscountRedemption::where('user_id', $user->id)
                ->where('code_id', $code->id)
                ->count();

            if ($userUsageCount >= $code->usage_limit_per_user) {
                return redirect()->back()
                    ->with('error', __('frontend.discount_redemptions.errors.user_limit_reached'))
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $redemption = DiscountRedemption::create([
                'discount_id' => $request->discount_id,
                'code_id' => $request->code_id,
                'order_id' => $request->order_id,
                'user_id' => $user->id,
                'amount_saved' => $request->amount_saved,
                'currency_code' => $request->currency_code,
                'status' => 'completed',
                'redeemed_at' => now(),
                'notes' => $request->notes,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Update code usage count
            $code->increment('usage_count');

            // Update discount usage count
            $discount->increment('usage_count');

            DB::commit();

            return redirect()->route('frontend.discount-redemptions.show', $redemption)
                ->with('success', __('frontend.discount_redemptions.messages.created_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', __('frontend.discount_redemptions.errors.creation_failed'))
                ->withInput();
        }
    }

    /**
     * Get available discount codes for a specific discount
     */
    public function getDiscountCodes(Request $request): JsonResponse
    {
        $discountId = $request->input('discount_id');

        if (! $discountId) {
            return response()->json([]);
        }

        $codes = DiscountCode::where('discount_id', $discountId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->get(['id', 'code', 'description_lt', 'description_en', 'usage_limit', 'usage_count'])
            ->skipWhile(function ($code) {
                // Skip codes that are not properly configured for display
                return empty($code->code) || 
                       empty($code->id) ||
                       !$code->is_active;
            });

        return response()->json($codes);
    }

    /**
     * Get user's redemption statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total_redemptions' => DiscountRedemption::where('user_id', $user->id)->count(),
            'total_saved' => DiscountRedemption::where('user_id', $user->id)->sum('amount_saved'),
            'average_saved' => DiscountRedemption::where('user_id', $user->id)->avg('amount_saved') ?? 0,
            'this_month_redemptions' => DiscountRedemption::where('user_id', $user->id)
                ->whereMonth('redeemed_at', now()->month)
                ->whereYear('redeemed_at', now()->year)
                ->count(),
            'this_week_redemptions' => DiscountRedemption::where('user_id', $user->id)
                ->whereBetween('redeemed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export user's redemptions to CSV
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $user = Auth::user();

        $query = DiscountRedemption::with(['discount', 'code', 'order'])
            ->where('user_id', $user->id);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('discount_id')) {
            $query->where('discount_id', $request->discount_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('redeemed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('redeemed_at', '<=', $request->date_to);
        }

        $redemptions = $query->orderBy('redeemed_at', 'desc')->get();

        $filename = 'discount_redemptions_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($redemptions) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                __('frontend.discount_redemptions.export.id'),
                __('frontend.discount_redemptions.export.discount'),
                __('frontend.discount_redemptions.export.code'),
                __('frontend.discount_redemptions.export.order'),
                __('frontend.discount_redemptions.export.amount_saved'),
                __('frontend.discount_redemptions.export.currency'),
                __('frontend.discount_redemptions.export.status'),
                __('frontend.discount_redemptions.export.redeemed_at'),
                __('frontend.discount_redemptions.export.notes'),
            ]);

            // CSV data
            foreach ($redemptions as $redemption) {
                fputcsv($handle, [
                    $redemption->id,
                    $redemption->discount->name ?? '',
                    $redemption->code->code ?? '',
                    $redemption->order->order_number ?? '',
                    $redemption->amount_saved,
                    $redemption->currency_code,
                    $redemption->status,
                    $redemption->redeemed_at?->format('Y-m-d H:i:s'),
                    $redemption->notes ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
