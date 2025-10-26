<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\ApiKey;
use App\Models\Order;
use App\Models\Partner;
use App\Support\Contracts\Entities\OrderContract;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class OrdersIndexController
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('partner_api_key');

        // Ensure that the upstream middleware resolved a partner API key instance.
        if (! $apiKey instanceof ApiKey) {
            return response()->json([
                'message' => 'Partner API key context is missing.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** @var Partner|null $partner */
        $partner = $apiKey->partner;

        // Guard against misconfigured credentials that are not linked to any partner.
        if (! $partner instanceof Partner) {
            return response()->json([
                'message' => 'Partner configuration is missing for this API key.',
            ], Response::HTTP_CONFLICT);
        }

        $abilities = array_values(array_filter(
            (array) $request->attributes->get('partner_api_abilities', []),
            static fn ($scope): bool => is_string($scope) && $scope !== ''
        ));
        $requiredScopes = array_values(array_filter(
            (array) $request->attributes->get('partner_api_required_scopes', []),
            static fn ($scope): bool => is_string($scope) && $scope !== ''
        ));

        if ($requiredScopes === []) {
            // Default to the canonical orders scope when upstream middleware did not specify any.
            $requiredScopes = ['orders.read'];
        }

        /** @var list<string> $requiredScopes */
        if (! $apiKey->hasAnyScope($requiredScopes)) {
            // Mirror legacy messaging when the legacy pipeline is active to preserve compatibility.
            $pipelineAttribute = $request->attributes->get('partner_api_pipeline', 'modern');
            $pipeline = is_string($pipelineAttribute) && $pipelineAttribute !== ''
                ? $pipelineAttribute
                : 'modern';
            $message = $pipeline === 'legacy'
                ? 'Forbidden.'
                : 'Insufficient partner API permissions.';

            return response()->json([
                'message' => $message,
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            // Validate supported query parameters so the endpoint fails fast on invalid filters.
            /**
             * @var array{
             *     status?: string,
             *     payment_status?: string,
             *     since?: string,
             *     until?: string,
             *     per_page?: int,
             *     page?: int,
             * } $validated
             */
            $validated = Validator::make($request->query(), [
                'status'         => ['sometimes', 'string', Rule::in(OrderStatus::values())],
                'payment_status' => ['sometimes', 'string', Rule::in($this->allowedPaymentStatuses())],
                'since'          => ['sometimes', 'date'],
                'until'          => ['sometimes', 'date'],
                'per_page'       => ['sometimes', 'integer', 'between:1,100'],
                'page'           => ['sometimes', 'integer', 'min:1'],
            ])->validate();
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Invalid query parameters provided.',
                'errors'  => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Normalise optional date filters into immutable Carbon instances for consistent comparisons.
        $sinceInput = $validated['since'] ?? null;
        $untilInput = $validated['until'] ?? null;

        $since = $sinceInput !== null ? CarbonImmutable::parse($sinceInput) : null;
        $until = $untilInput !== null ? CarbonImmutable::parse($untilInput) : null;

        if ($since instanceof \Carbon\CarbonImmutable && $until instanceof \Carbon\CarbonImmutable && $since->gt($until)) {
            return response()->json([
                'message' => 'The "since" parameter must be earlier than or equal to "until".',
                'errors'  => ['since' => ['The provided range is invalid.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Build the scoped order query so partners only see their own records.
        $query = Order::query()
            ->with(['items'])
            ->where('partner_id', $partner->getKey())
            ->orderByDesc('created_at');

        $statusFilter = $this->normaliseOrderStatus($validated['status'] ?? null);

        if ($statusFilter instanceof OrderStatus) {
            // Apply lifecycle status filtering when requested using the enum to guarantee valid values.
            $query->where('status', $statusFilter->value);
        }

        $paymentFilter = $this->normalisePaymentStatus($validated['payment_status'] ?? null);

        if ($paymentFilter instanceof PaymentStatus) {
            // Allow partners to narrow down by payment status for reconciliation workflows.
            $query->where('payment_status', $paymentFilter->value);
        }

        if ($since instanceof \Carbon\CarbonImmutable) {
            // Limit results to orders placed after the provided lower bound.
            $query->where('created_at', '>=', $since);
        }

        if ($until instanceof \Carbon\CarbonImmutable) {
            // Limit results to orders placed before the provided upper bound.
            $query->where('created_at', '<=', $until);
        }

        $perPage = $validated['per_page'] ?? 25;
        $page = $validated['page'] ?? 1;

        /** @var LengthAwarePaginator<int, Order> $paginator */
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Capture key partner metadata while respecting nullable database columns.
        /** @var string|null $partnerCode */
        $partnerCode = $partner->getAttribute('code');
        /** @var string|null $partnerName */
        $partnerName = $partner->getAttribute('name');

        // Shape the response using the public order contract so payloads remain backward compatible.
        $payload = OrderContract::forCollection($paginator->getCollection(), [
            'scopes'          => $abilities,
            'required_scopes' => $requiredScopes,
            'partner'         => [
                'id'   => $partner->getKey(),
                'code' => $partnerCode,
                'name' => $partnerName,
            ],
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
            'filters' => Arr::whereNotNull([
                'status'         => $statusFilter?->value,
                'payment_status' => $paymentFilter?->value,
                'since'          => $since?->toIso8601String(),
                'until'          => $until?->toIso8601String(),
            ]),
        ]);

        return response()->json($payload);
    }

    /**
     * Normalise the requested order status filter against the enum.
     */
    private function normaliseOrderStatus(?string $status): ?OrderStatus
    {
        if ($status === null || $status === '') {
            return null;
        }

        return OrderStatus::tryFrom($status);
    }

    /**
     * Normalise the requested payment status filter against the enum.
     */
    private function normalisePaymentStatus(?string $status): ?PaymentStatus
    {
        if ($status === null || $status === '') {
            return null;
        }

        return PaymentStatus::tryFrom($status);
    }

    /**
     * Provide the list of allowed payment status filters for validator rules.
     *
     * @return array<int, string>
     */
    private function allowedPaymentStatuses(): array
    {
        return collect(PaymentStatus::cases())
            ->map(static fn (PaymentStatus $status): string => $status->value)
            ->toArray();
    }
}
