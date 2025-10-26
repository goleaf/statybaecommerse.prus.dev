<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Discounts\DiscountContextBuilder;
use App\Services\Discounts\DiscountEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

use function array_key_exists;
use function data_get;
use function max;
use function round;

/**
 * DiscountPreviewController
 *
 * HTTP controller handling DiscountPreviewController related web requests,
 * responses, and business logic with proper validation and error handling.
 */
final class DiscountPreviewController extends Controller
{
    /**
     * Create a new controller instance with the collaborating services.
     */
    public function __construct(
        private readonly DiscountContextBuilder $contextBuilder,
        private readonly DiscountEngine $discountEngine,
    ) {
    }

    /**
     * Generate a preview for discount effects based on the provided payload.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Ensure the acting administrator has permission to inspect discount behaviour.
        Gate::authorize('discount_conditions.viewAny');

        // Validate the preview payload so only well-formed data flows into the engine.
        $validated = $request->validate([
            'code'                      => ['nullable', 'string', 'max:255'],
            'cart'                      => ['nullable', 'array'],
            'cart.subtotal'             => ['nullable', 'numeric', 'min:0'],
            'cart.items'                => ['nullable', 'array'],
            'cart.items.*.product_id'   => ['nullable', 'integer', 'min:1'],
            'cart.items.*.variant_id'   => ['nullable', 'integer', 'min:1'],
            'cart.items.*.quantity'     => ['nullable', 'integer', 'min:0'],
            'cart.items.*.unit_price'   => ['nullable', 'numeric', 'min:0'],
            'shipping'                  => ['nullable', 'array'],
            'shipping.base_amount'      => ['nullable', 'numeric', 'min:0'],
        ]);

        // Build a sanitized input array so the downstream builder only receives trusted values.
        $sanitizedInput = [
            'code' => $validated['code'] ?? null,
            'cart' => $validated['cart'] ?? [],
        ];

        // Only include the shipping data if it was provided so defaults remain intact otherwise.
        if (array_key_exists('shipping', $validated) && is_array($validated['shipping'] ?? null)) {
            $shippingPayload = [];

            if (array_key_exists('base_amount', $validated['shipping'])) {
                $shippingPayload['base_amount'] = $validated['shipping']['base_amount'];
            }

            if ($shippingPayload !== []) {
                $sanitizedInput['shipping'] = $shippingPayload;
            }
        }

        // Clone the original request with the sanitized payload while preserving the user resolver.
        $sanitizedRequest = $request->duplicate(
            $request->query->all(),
            $sanitizedInput,
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
        );
        $sanitizedRequest->setUserResolver($request->getUserResolver());
        $sanitizedRequest->setRouteResolver($request->getRouteResolver());

        try {
            // Convert the incoming payload into the detailed evaluation context structure.
            $context = $this->contextBuilder->fromRequest($sanitizedRequest, $sanitizedInput['code']);

            // Execute the discount engine so we can surface the simulated adjustments.
            $result = $this->discountEngine->evaluate($context);
        } catch (Throwable $exception) {
            // Report the unexpected failure and surface a friendly validation error.
            report($exception);

            throw ValidationException::withMessages([
                'preview' => __('Unable to build the discount preview at this time.'),
            ]);
        }

        // Summarize the totals so the UI can present a concise breakdown.
        $subtotal = (float) data_get($context, 'cart.subtotal', 0);
        $shippingBase = (float) data_get($context, 'shipping.base_amount', 0);
        $discountAmount = (float) data_get($result, 'discount_total_amount', 0);
        $shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0);
        $total = max($subtotal + $shippingBase - $discountAmount - $shippingDiscount, 0);

        // Return the computed preview payload ready for consumption by the admin UI.
        return response()->json([
            'success' => true,
            'context' => $context,
            'result'  => $result,
            'summary' => [
                'subtotal'           => round($subtotal, 2),
                'shipping'           => round($shippingBase, 2),
                'discount'           => round($discountAmount, 2),
                'shipping_discount'  => round($shippingDiscount, 2),
                'total'              => round($total, 2),
            ],
        ]);
    }
}
