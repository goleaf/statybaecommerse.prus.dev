<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DiscountConditionIndexRequest;
use App\Http\Requests\DiscountConditionOperatorsRequest;
use App\Http\Resources\DiscountConditionCollection;
use App\Http\Resources\DiscountConditionOperatorCollection;
use App\Http\Resources\DiscountConditionTestResource;
use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use OpenApi\Attributes as OA;

/**
 * DiscountConditionController
 *
 * HTTP controller handling DiscountConditionController related web requests, responses, and business logic with proper validation and error handling.
 */
#[OA\Tag(name: 'Discount Conditions', description: 'Discount condition evaluation and metadata endpoints.')]
final class DiscountConditionController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(DiscountConditionIndexRequest $request): View
    {
        Gate::authorize('viewAny', DiscountCondition::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->validated();

        // Warm up the query with eager loading to avoid N+1 lookups when rendering the table.
        $query = DiscountCondition::query()
            ->with(['discount', 'translations'])
            ->active();

        $typeFilter = $filters['type'] ?? null;
        if (is_string($typeFilter) && $typeFilter !== '') {
            // Allow narrowing by discriminator type.
            $query->byType($typeFilter);
        }

        if (array_key_exists('discount_id', $filters)) {
            $discountId = $filters['discount_id'];

            // Allow targeting a single discount when auditing its rules.
            if (is_int($discountId) || (is_string($discountId) && $discountId !== '')) {
                $query->where('discount_id', (int) $discountId);
            }
        }

        $operatorFilter = $filters['operator'] ?? null;
        if (is_string($operatorFilter) && $operatorFilter !== '') {
            // Allow filtering by comparison operator for diagnostic purposes.
            $query->byOperator($operatorFilter);
        }

        $sort = $filters['sort'] ?? 'priority';
        if (! is_string($sort) || $sort === '') {
            $sort = 'priority';
        }

        $direction = $filters['direction'] ?? 'desc';
        if (! is_string($direction) || $direction === '') {
            $direction = 'desc';
        }

        if ($sort === 'priority') {
            // Retain the historical default priority ordering.
            $query->orderBy('priority', $direction);
        } else {
            // Provide deterministic secondary ordering to ensure stable pagination.
            $query->orderBy($sort, $direction)->orderBy('id');
        }

        $perPageInput = $filters['per_page'] ?? null;
        $perPage = 20;

        if (is_int($perPageInput)) {
            // Trust integer values coming from the validated payload.
            $perPage = max(1, $perPageInput);
        } elseif (is_string($perPageInput) && $perPageInput !== '') {
            // Normalise numeric strings into integers for the paginator.
            $perPage = max(1, (int) $perPageInput);
        }

        $conditions = $query
            ->paginate($perPage)
            ->appends($filters);

        $discounts = Discount::query()->active()->orderBy('name')->get();
        $types = DiscountCondition::getTypes();
        $operators = DiscountCondition::getOperators();

        return view('discount-conditions.index', ['conditions' => $conditions, 'discounts' => $discounts, 'types' => $types, 'operators' => $operators]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(DiscountCondition $discountCondition): View
    {
        Gate::authorize('view', $discountCondition);

        // Ensure relationships are loaded for the detailed view without incurring N+1 queries.
        $discountCondition->loadMissing(['discount', 'translations']);

        return view('discount-conditions.show', ['discountCondition' => $discountCondition]);
    }

    #[OA\Post(
        path: '/discount-conditions/{discountCondition}/test',
        summary: 'Test a discount condition',
        description: 'Evaluate a discount condition against a provided test value to determine whether it matches and is valid.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\Parameter(name: 'discountCondition', description: 'Discount condition identifier.', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['test_value'], properties: [
                new OA\Property(property: 'test_value', type: 'string'),
            ], type: 'object')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Condition test results.', content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionTestResponse')),
            new OA\Response(response: 422, description: 'Validation failed.', content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')),
        ],
        servers: [new OA\Server(url: '/')]
    )]
    /**
     * Handle test functionality with proper error handling.
     */
    #[OA\Post(
        path: '/discount-conditions/{discountCondition}/test',
        summary: 'Evaluate whether a payload satisfies the discount condition.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\PathParameter(
                name: 'discountCondition',
                description: 'Discount condition identifier.',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Value to test against the condition.',
            required: true,
            content: new OA\JsonContent(
                required: ['test_value'],
                properties: [
                    new OA\Property(property: 'test_value', type: 'string'),
                ],
                type: 'object',
            ),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/DiscountConditionTest', response: 200),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
        ]
    )]
    public function test(\App\Http\Requests\DiscountConditionTestRequest $request, DiscountCondition $discountCondition): JsonResponse
    {
        Gate::authorize('view', $discountCondition);

        $validated = $request->validated();
        $value = $validated['test_value'];
        $matches = $discountCondition->matches($value);
        $isValid = $discountCondition->isValidForContext([$discountCondition->type => $value]);

        // Delegate the JSON structure to an API resource so formatting stays consistent.
        return DiscountConditionTestResource::make([
            'matches'               => $matches,
            'is_valid'              => $isValid,
            'condition_description' => $discountCondition->human_readable_condition,
            'message'               => $matches
                ? __('discount_conditions.messages.condition_matches')
                : __('discount_conditions.messages.condition_does_not_match'),
        ])->toResponse($request);
    }

    #[OA\Get(
        path: '/discount-conditions/api/for-discount/{discount}',
        summary: 'List conditions for a discount',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\Parameter(name: 'discount', description: 'Discount identifier.', in: 'path', required: true, schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Discount conditions.', content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionCollection')),
        ],
        servers: [new OA\Server(url: '/')]
    )]
    /**
     * Handle forDiscount functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/for-discount/{discount}',
        summary: 'List active discount conditions for the given discount.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\PathParameter(
                name: 'discount',
                description: 'Discount identifier.',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/DiscountConditions', response: 200),
        ]
    )]
    public function forDiscount(Discount $discount): JsonResponse
    {
        Gate::authorize('viewAny', DiscountCondition::class);

        $conditions = DiscountCondition::query()
            ->where('discount_id', $discount->getKey())
            ->active()
            ->byPriority('desc')
            ->with('translations')
            ->get();

        // Reuse the resource collection so the JSON payload stays consistent across endpoints.
        return DiscountConditionCollection::make($conditions)->response();
    }

    #[OA\Get(
        path: '/discount-conditions/api/operators-for-type',
        summary: 'List operators for a discount condition type',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\QueryParameter(name: 'type', description: 'Discount condition type key.', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Available operators.', content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionOperatorsResponse')),
        ],
        servers: [new OA\Server(url: '/')]
    )]
    /**
     * Handle operatorsForType functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/operators-for-type',
        summary: 'List supported operators for a discount condition type.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\QueryParameter(
                name: 'type',
                description: 'Condition type key.',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/DiscountConditionOperators', response: 200),
        ]
    )]
    public function operatorsForType(DiscountConditionOperatorsRequest $request): JsonResponse
    {
        Gate::authorize('viewAny', DiscountCondition::class);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();
        $type = $validated['type'] ?? null;

        if (! is_string($type) || $type === '') {
            // When the discriminator is absent, expose the global operator mapping.
            $operators = DiscountCondition::getOperators();
        } else {
            // Hand back the subset relevant to the requested discriminator.
            $operators = DiscountCondition::getOperatorsForType($type);
        }

        // Convert the associative list into a simple key/label structure for UI clients.
        $operatorCollection = collect($operators)
            ->map(static fn (mixed $label, int|string $key): array => [
                'key'   => is_int($key) ? (string) $key : $key,
                'label' => is_string($label) ? $label : (is_scalar($label) ? strval($label) : ''),
            ])
            ->values();

        return DiscountConditionOperatorCollection::make($operatorCollection)->response();
    }

    #[OA\Get(
        path: '/discount-conditions/api/statistics',
        summary: 'Aggregate discount condition statistics',
        tags: ['Discount Conditions'],
        responses: [
            new OA\Response(response: 200, description: 'Discount condition statistics.', content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionStatistics')),
        ],
        servers: [new OA\Server(url: '/')]
    )]
    /**
     * Handle statistics functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/statistics',
        summary: 'Summarize discount condition usage metrics.',
        tags: ['Discount Conditions'],
        responses: [
            new OA\Response(ref: '#/components/responses/DiscountConditionStatistics', response: 200),
        ]
    )]
    public function statistics(): JsonResponse
    {
        $stats = ['total' => DiscountCondition::count(), 'active' => DiscountCondition::where('is_active', true)->count(), 'inactive' => DiscountCondition::where('is_active', false)->count(), 'by_type' => DiscountCondition::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(), 'by_operator' => DiscountCondition::selectRaw('operator, COUNT(*) as count')->groupBy('operator')->pluck('count', 'operator')->toArray()];

        return response()->json($stats);
    }
}
