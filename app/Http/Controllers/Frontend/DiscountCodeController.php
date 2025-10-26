<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Data\DiscountCodeValidationData;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\DocumentTemplate;
use App\Services\Discounts\DiscountContextBuilder;
use App\Services\Discounts\DiscountEngine;
use App\Services\DocumentService;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * DiscountCodeController
 *
 * HTTP controller handling DiscountCodeController related web requests, responses, and business logic with proper validation and error handling.
 */
final class DiscountCodeController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly DiscountContextBuilder $contextBuilder,
        private readonly DiscountEngine $discountEngine,
    ) {}

    /**
     * Consistently shape JSON error responses for discount code actions.
     */
    private function failure(string $reason, string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'reason'  => $reason,
            'message' => $message,
        ], $status);
    }

    /**
     * Validate the input data against defined rules.
     */
    public function validate(DiscountCodeValidationData $data): JsonResponse
    {
        $code = DiscountCode::query()
            ->with('discount')
            ->withCode($data->code)
            ->first();
        if (! $code || ! $code->discount instanceof Discount) {
            return response()->json(['valid' => false, 'message' => __('discount_code_invalid')], 422);
        }
        if (! $code->isValid()) {
            $expiresAt = $code->expires_at instanceof CarbonInterface ? $code->expires_at : null;
            $message = match (true) {
                $code->hasReachedLimit()                                       => __('discount_code_limit_reached'),
                $expiresAt instanceof CarbonInterface && $expiresAt->lt(now()) => __('discount_code_expired_message'),
                ! $code->is_active                                             => __('discount_code_inactive'),
                default                                                        => __('discount_code_invalid'),
            };

            return response()->json(['valid' => false, 'message' => $message], 422);
        }
        // Check if user has already used this code (if limit per user is set)
        if ($code->usage_limit_per_user && Auth::check()) {
            $userUsage = DiscountRedemption::where('code_id', $code->id)->where('user_id', Auth::id())->count();
            if ($userUsage >= $code->usage_limit_per_user) {
                return response()->json(['valid' => false, 'message' => __('discount_code_already_used')], 422);
            }
        }

        return response()->json(['valid' => true, 'message' => __('discount_code_success'), 'discount' => ['id' => $code->id, 'code' => $code->code, 'name' => $code->discount->name, 'type' => $code->discount->type, 'value' => $code->discount->value, 'description' => $code->description]]);
    }

    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(\App\Http\Requests\Frontend\DiscountCodeApplyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $normalized = mb_strtoupper(trim($validated['code']));

        // Load the code alongside its backing discount so downstream checks stay atomic.
        $code = DiscountCode::query()
            ->with('discount')
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (! $code || ! $code->discount instanceof Discount) {
            return $this->failure('not_found', __('discount_code_invalid'), 422);
        }

        $discount = $code->discount;
        $now = now();

        // Guard against stale or future-dated codes before performing heavier calculations.
        if (! $code->is_active) {
            return $this->failure('inactive', __('discount_code_invalid'), 422);
        }
        if ($code->starts_at && $code->starts_at->gt($now)) {
            return $this->failure('inactive', __('discount_code_invalid'), 422);
        }
        if ($code->expires_at && $code->expires_at->lt($now)) {
            return $this->failure('expired', __('discount_code_expired_message'), 422);
        }
        if ($code->hasReachedLimit()) {
            return $this->failure('usage_limit', __('discount_code_limit_reached'), 409);
        }

        // Mirror the checks for the parent discount so legacy codes cannot bypass discount-level limits.
        if (! $discount->isValid()) {
            return $this->failure('inactive', __('discount_code_invalid'), 422);
        }
        if ($discount->hasReachedLimit()) {
            return $this->failure('usage_limit', __('discount_code_limit_reached'), 409);
        }

        // Derive the pricing context from the incoming payload to guarantee server-side calculations.
        $context = $this->contextBuilder->fromRequest($request, $normalized);
        $subtotal = (float) data_get($context, 'cart.subtotal', 0.0);

        // Honour minimum basket requirements from both the code and the discount definition.
        $requiredMinimums = array_filter([
            $code->minimum_amount !== null ? (float) $code->minimum_amount : null,
            $discount->minimum_amount !== null ? (float) $discount->minimum_amount : null,
        ]);
        if ($requiredMinimums !== [] && $subtotal < max($requiredMinimums)) {
            return $this->failure('minimum_not_met', __('discount_code_minimum_not_met'), 422);
        }

        // Prevent mixing with other promotions when stackability rules disallow it.
        /** @var array<string, mixed>|null $activeCode */
        $activeCode = session('checkout.discount_code');
        if ($activeCode !== null && mb_strtoupper((string) ($activeCode['code'] ?? '')) !== $normalized) {
            $existingStackable = (bool) ($activeCode['is_stackable'] ?? false);
            if (! $existingStackable || ! $code->is_stackable) {
                return $this->failure('stacking', __('discount_code_not_stackable'), 409);
            }
        }
        /** @var array<string, mixed>|null $activeCoupon */
        $activeCoupon = session('checkout.coupon');
        if ($activeCoupon !== null && ! $code->is_stackable) {
            return $this->failure('stacking', __('discount_code_not_stackable'), 409);
        }

        /** @var int|null $userId */
        $userId = Auth::id();
        $blockingStatuses = ['pending', 'redeemed'];

        // Enforce single-use semantics per user when either the code or discount carries such a rule.
        if ($userId !== null && $code->usage_limit_per_user) {
            $userUsage = DiscountRedemption::query()
                ->where('code_id', $code->getKey())
                ->where('user_id', $userId)
                ->whereIn('status', $blockingStatuses)
                ->count();
            if ($userUsage >= (int) $code->usage_limit_per_user) {
                return $this->failure('per_user_limit', __('discount_code_already_used'), 409);
            }
        }
        if ($userId !== null && $discount->per_customer_limit) {
            $discountUsage = DiscountRedemption::query()
                ->where('discount_id', $discount->getKey())
                ->where('user_id', $userId)
                ->whereIn('status', $blockingStatuses)
                ->count();
            if ($discountUsage >= (int) $discount->per_customer_limit) {
                return $this->failure('per_user_limit', __('discount_code_already_used'), 409);
            }
        }

        // Ask the discount engine to recompute the current cart totals with this code in scope.
        $pricing = $this->discountEngine->evaluate($context);
        $applied = collect($pricing['applied'] ?? [])->firstWhere('id', $discount->getKey());
        $discountAmount = $applied ? (float) ($applied['amount'] ?? 0.0) : 0.0;
        $shippingDiscount = (float) data_get($pricing, 'shipping.discount_amount', 0.0);
        $totalBenefit = round($discountAmount + $shippingDiscount, 2);

        if (! $applied || $totalBenefit <= 0.0) {
            return $this->failure('not_applicable', __('discount_code_not_applicable'), 422);
        }

        // Persist or refresh the pending ledger entry so audits can trace redemption attempts.
        $ledgerMetadata = [
            'source'       => 'frontend.apply',
            'context_hash' => hash('sha256', (string) json_encode([
                'subtotal' => $subtotal,
                'items'    => count((array) data_get($context, 'cart.items', [])),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR)),
        ];

        $ledger = DiscountRedemption::query()
            ->where('discount_id', $discount->getKey())
            ->where('code_id', $code->getKey())
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->first();

        if ($ledger) {
            $ledger->forceFill([
                'amount_saved'  => round($discountAmount, 2),
                'currency_code' => current_currency(),
                'metadata'      => array_merge($ledger->metadata ?? [], $ledgerMetadata),
                'redeemed_at'   => null,
                'status'        => 'pending',
                'ip_address'    => $request->ip(),
                'user_agent'    => (string) $request->userAgent(),
            ])->save();
        } else {
            $ledger = DiscountRedemption::create([
                'discount_id'   => $discount->getKey(),
                'code_id'       => $code->getKey(),
                'order_id'      => $validated['order_id'] ?? null,
                'user_id'       => $userId,
                'amount_saved'  => round($discountAmount, 2),
                'currency_code' => current_currency(),
                'redeemed_at'   => null,
                'status'        => 'pending',
                'metadata'      => $ledgerMetadata,
                'ip_address'    => $request->ip(),
                'user_agent'    => (string) $request->userAgent(),
            ]);
        }

        $payload = [
            'id'                => $code->getKey(),
            'code'              => $code->code,
            'discount_id'       => $discount->getKey(),
            'discount_amount'   => round($discountAmount, 2),
            'shipping_discount' => round($shippingDiscount, 2),
            'is_stackable'      => (bool) $code->is_stackable,
            'ledger_id'         => $ledger?->getKey(),
            'pricing'           => $pricing,
        ];

        session()->put('checkout.discount_code', $payload);

        return response()->json([
            'success'       => true,
            'reason'        => null,
            'message'       => __('discount_code_success'),
            'discount_code' => $payload,
        ]);
    }

    /**
     * Handle remove functionality with proper error handling.
     */
    public function remove(\App\Http\Requests\Frontend\DiscountCodeRemoveRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $code = DiscountCode::where('code', $validated['code'])->first();
        if (! $code) {
            return response()->json(['success' => false, 'message' => __('discount_code_invalid')], 422);
        }
        // Find and mark the pending ledger entry so history remains intact while freeing the code.
        $redemption = DiscountRedemption::query()
            ->where('code_id', $code->id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->latest()
            ->first();
        if ($redemption) {
            $redemption->forceFill([
                'status'      => 'cancelled',
                'redeemed_at' => null,
            ])->save();
        }

        session()->forget('checkout.discount_code');

        return response()->json(['success' => true, 'message' => __('discount_code_removed')]);
    }

    /**
     * Handle available functionality with proper error handling.
     */
    public function available(Request $request): JsonResponse
    {
        /** @var int|null $userId */
        $userId = Auth::id();

        // Build the base query so we can apply eager loading and guard against
        // incomplete configurations directly in SQL instead of filtering later.
        $availableCodesQuery = DiscountCode::query()
            ->active()
            ->with('discount')
            ->whereHas('discount')
            ->whereNotNull('code')
            ->where('code', '<>', '');

        if ($userId !== null) {
            $availableCodesQuery->withCount([
                'redemptions as user_redemptions_count' => static function (Builder $builder) use ($userId): void {
                    $builder->where('user_id', $userId);
                },
            ]);
        }

        /** @var \Illuminate\Support\Collection<int, DiscountCode> $availableCodes */
        $availableCodes = $availableCodesQuery->get()->filter(static function (DiscountCode $code) use ($userId): bool {
            // Discard codes that lost their discount relationship due to
            // synchronisation or data clean-up tasks.
            if (! $code->discount instanceof Discount) {
                return false;
            }

            if ($userId !== null && $code->usage_limit_per_user) {
                $usageCount = (int) ($code->user_redemptions_count ?? 0);

                return $usageCount < $code->usage_limit_per_user;
            }

            return true;
        });

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $codes */
        $codes = $availableCodes->map(static function (DiscountCode $code): array {
            /** @var Discount $discount */
            $discount = $code->discount;
            $expiresAt = $code->expires_at instanceof CarbonInterface ? $code->expires_at : null;

            return [
                'id'          => $code->id,
                'code'        => $code->code,
                'description' => $code->description,
                'discount'    => [
                    'name'  => $discount->name,
                    'type'  => $discount->type,
                    'value' => $discount->value,
                ],
                'expires_at'     => $expiresAt?->format('d/m/Y'),
                'remaining_uses' => $code->remaining_uses,
            ];
        })->values();

        return response()->json(['codes' => $codes->all()]);
    }

    /**
     * Handle generateDocument functionality with proper error handling.
     */
    public function generateDocument(\App\Http\Requests\Frontend\DiscountCodeGenerateDocumentRequest $request, DiscountCode $discountCode): JsonResponse|Response|RedirectResponse
    {
        /** @var array{template_id:int, format:string} $validated */
        $validated = $request->validated();
        try {
            $template = DocumentTemplate::query()->findOrFail((int) $validated['template_id']);
            $discountCode->loadMissing('discount');

            if (! $discountCode->discount instanceof Discount) {
                return response()->json([
                    'error'   => 'Discount data unavailable',
                    'message' => __('discount_code_invalid'),
                ], 422);
            }

            $discount = $discountCode->discount;
            $startsAt = $discountCode->starts_at instanceof CarbonInterface ? $discountCode->starts_at : null;
            $expiresAt = $discountCode->expires_at instanceof CarbonInterface ? $discountCode->expires_at : null;

            // Prepare the variable map that will be injected into the template.
            $variables = [
                'DISCOUNT_CODE'        => $discountCode->code,
                'DISCOUNT_NAME'        => $discount->name,
                'DISCOUNT_DESCRIPTION' => $discountCode->description,
                'DISCOUNT_VALUE'       => $discount->value,
                'DISCOUNT_TYPE'        => $discount->type,
                'USAGE_LIMIT'          => $discountCode->usage_limit ?? 'Unlimited',
                'USAGE_COUNT'          => $discountCode->usage_count,
                'REMAINING_USES'       => $discountCode->remaining_uses ?? 'Unlimited',
                'STARTS_AT'            => $startsAt?->format('d/m/Y H:i') ?? 'Immediately',
                'EXPIRES_AT'           => $expiresAt?->format('d/m/Y H:i') ?? 'Never',
                'STATUS'               => $discountCode->status,
                'IS_ACTIVE'            => $discountCode->is_active ? 'Yes' : 'No',
            ];

            $document = $this->documentService->generateDocument($template, $discountCode, $variables);

            if ($validated['format'] === 'pdf') {
                // Generate a signed download URL for the generated PDF payload and
                // return an appropriate response based on the caller expectations.
                $downloadUrl = $this->documentService->generatePdf($document);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success'      => true,
                        'document_id'  => $document->id,
                        'download_url' => $downloadUrl,
                    ]);
                }

                return redirect()->away($downloadUrl);
            }

            return response($document->content, 200, ['Content-Type' => 'text/html']);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'error'   => 'Template not found',
                'message' => $exception->getMessage(),
            ], 404);
        } catch (Throwable $e) {
            return response()->json(['error' => 'Failed to generate document', 'message' => $e->getMessage()], 500);
        }
    }
}
