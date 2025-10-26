<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function index(Request $request): View
    {
        $query = DiscountCondition::with(['discount', 'translations'])->active()->byPriority('desc');
        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }
        if ($request->filled('discount_id')) {
            $query->where('discount_id', $request->get('discount_id'));
        }
        if ($request->filled('operator')) {
            $query->byOperator($request->get('operator'));
        }
        $conditions = $query
            // Filter out discount conditions that are missing the key attributes required for the index view.
            ->whereNotNull('type')
            ->whereNotNull('discount_id')
            ->whereNotNull('operator')
            ->whereHas('discount')
            ->paginate(20);
        $discounts = Discount::active()->get();
        $types = DiscountCondition::getTypes();
        $operators = DiscountCondition::getOperators();

        return view('discount-conditions.index', compact('conditions', 'discounts', 'types', 'operators'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(DiscountCondition $discountCondition): View
    {
        $discountCondition->load(['discount', 'translations']);

        return view('discount-conditions.show', compact('discountCondition'));
    }

    #[OA\Post(
        path: '/discount-conditions/{discountCondition}/test',
        summary: 'Test a discount condition',
        description: 'Evaluate a discount condition against a provided test value to determine whether it matches and is valid.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\Parameter(name: 'discountCondition', in: 'path', required: true, description: 'Discount condition identifier.', schema: new OA\Schema(type: 'integer', format: 'int64')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object', required: ['test_value'], properties: [
                new OA\Property(property: 'test_value', type: 'string'),
            ])
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
            required: true,
            description: 'Value to test against the condition.',
            content: new OA\JsonContent(
                type: 'object',
                required: ['test_value'],
                properties: [
                    new OA\Property(property: 'test_value', type: 'string'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/DiscountConditionTest'),
            new OA\Response(response: 422, ref: '#/components/responses/ValidationError'),
        ]
    )]
    public function test(\App\Http\Requests\DiscountConditionTestRequest $request, DiscountCondition $discountCondition): JsonResponse
    {
        $validated = $request->validated();
        $value = $validated['test_value'];
        $matches = $discountCondition->matches($value);
        $isValid = $discountCondition->isValidForContext([$discountCondition->type => $value]);

        return response()->json(['matches' => $matches, 'is_valid' => $isValid, 'condition_description' => $discountCondition->human_readable_condition, 'message' => $matches ? __('discount_conditions.messages.condition_matches') : __('discount_conditions.messages.condition_does_not_match')]);
    }

    #[OA\Get(
        path: '/discount-conditions/api/for-discount/{discount}',
        summary: 'List conditions for a discount',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\Parameter(name: 'discount', in: 'path', required: true, description: 'Discount identifier.', schema: new OA\Schema(type: 'integer', format: 'int64')),
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
            new OA\Response(response: 200, ref: '#/components/responses/DiscountConditions'),
        ]
    )]
    public function forDiscount(Discount $discount): JsonResponse
    {
        $conditions = $discount->conditions()->active()->byPriority('desc')->with('translations')->get();

        return response()->json(['conditions' => $conditions->map(function ($condition) {
            return ['id' => $condition->id, 'type' => $condition->type, 'type_label' => $condition->getTypeLabel(), 'operator' => $condition->operator, 'operator_label' => $condition->getOperatorLabel(), 'value' => $condition->value, 'priority' => $condition->priority, 'position' => $condition->position, 'description' => $condition->human_readable_condition, 'name' => $condition->translated_name];
        })]);
    }

    #[OA\Get(
        path: '/discount-conditions/api/operators-for-type',
        summary: 'List operators for a discount condition type',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\QueryParameter(name: 'type', in: 'query', description: 'Discount condition type key.', schema: new OA\Schema(type: 'string')),
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
            new OA\Response(response: 200, ref: '#/components/responses/DiscountConditionOperators'),
        ]
    )]
    public function operatorsForType(Request $request): JsonResponse
    {
        $type = $request->get('type');
        if (! $type) {
            return response()->json(['operators' => []]);
        }
        $operators = DiscountCondition::getOperatorsForType($type);

        return response()->json(['operators' => $operators]);
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
            new OA\Response(response: 200, ref: '#/components/responses/DiscountConditionStatistics'),
        ]
    )]
    public function statistics(): JsonResponse
    {
        $stats = ['total' => DiscountCondition::count(), 'active' => DiscountCondition::where('is_active', true)->count(), 'inactive' => DiscountCondition::where('is_active', false)->count(), 'by_type' => DiscountCondition::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(), 'by_operator' => DiscountCondition::selectRaw('operator, COUNT(*) as count')->groupBy('operator')->pluck('count', 'operator')->toArray()];

        return response()->json($stats);
    }
}
