<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeterministicTotalsRequest;
use App\Services\Pricing\DeterministicTotalsService;
use App\Services\Pricing\Exceptions\RateTamperingException;
use Illuminate\Http\JsonResponse;

final class DeterministicTotalsController extends Controller
{
    public function __construct(private readonly DeterministicTotalsService $service) {}

    public function __invoke(DeterministicTotalsRequest $request): JsonResponse
    {
        try {
            $quote = $this->service->quote($request->validated());
        } catch (RateTamperingException $exception) {
            // Surface a deterministic conflict error so integrators can detect tampering immediately.
            return response()->json([
                'error' => 'rate_tampering_detected',
                'message' => __('Client supplied rate details do not match the authoritative configuration.'),
                'field' => $exception->field(),
            ], 409);
        }

        /** @var \App\Data\Pricing\PriceBreakdown $breakdown */
        $breakdown = $quote['breakdown'];

        $totals = [
            'subtotal' => $breakdown->subtotal,
            'discount' => $breakdown->discount,
            'taxable_amount' => $breakdown->taxableAmount,
            'tax' => $breakdown->tax,
            'shipping' => $breakdown->shipping,
            'total' => $breakdown->total,
            'currency' => $breakdown->currency,
            'vat_rate' => $breakdown->vatRate,
        ];

        $payload = [
            'contract' => 'deterministic-totals',
            'version' => 'v1',
            'data' => [
                'totals' => $totals,
                'components' => [
                    'tax' => $quote['tax_component'],
                    'shipping' => $quote['shipping_component'],
                    'discount' => [
                        'amount' => $breakdown->discount,
                    ],
                ],
                'rounding' => $quote['rounding'],
            ],
            'meta' => [
                'generated_at' => now()->toISOString(),
            ],
        ];

        // Delegate to Laravel's JSON response builder to avoid manual encoding edge cases while
        // preserving fractional formatting for zero values (for example 0.0 discounts).
        return response()->json($payload, 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }
}
