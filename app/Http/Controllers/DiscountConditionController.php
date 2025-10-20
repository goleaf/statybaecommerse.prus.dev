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
#[OA\Tag(name: 'Discount Conditions', description: 'Discount condition evaluation endpoints')]
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
        $conditions = $query->get()->skipWhile(function ($condition) {
            // Skip discount conditions that are not properly configured for display
            return empty($condition->type) || ! $condition->is_active || empty($condition->discount) || empty($condition->discount_id) || empty($condition->operator);
        })->paginate(20);
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

    /**
     * Handle test functionality with proper error handling.
     */
    #[OA\Post(
        path: '/discount-conditions/{discountCondition}/test',
        operationId: 'testDiscountCondition',
        summary: 'Evaluate whether a value satisfies a discount condition.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\PathParameter(
                name: 'discountCondition',
                description: 'Discount condition identifier.',
                required: true,
                schema: new OA\Schema(type: 'integer')
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
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Evaluation result returned.',
                content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionTestResponse')
            ),
        ]
    )]
    public function test(Request $request, DiscountCondition $discountCondition): JsonResponse
    {
        $request->validate(['test_value' => 'required']);
        $matches = $discountCondition->matches($request->get('test_value'));
        $isValid = $discountCondition->isValidForContext([$discountCondition->type => $request->get('test_value')]);

        return response()->json(['matches' => $matches, 'is_valid' => $isValid, 'condition_description' => $discountCondition->human_readable_condition, 'message' => $matches ? __('discount_conditions.messages.condition_matches') : __('discount_conditions.messages.condition_does_not_match')]);
    }

    /**
     * Handle forDiscount functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/for-discount/{discount}',
        operationId: 'listDiscountConditionsForDiscount',
        summary: 'List conditions attached to a discount.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\PathParameter(
                name: 'discount',
                description: 'Discount identifier.',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Conditions returned.',
                content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionCollection')
            ),
        ]
    )]
    public function forDiscount(Discount $discount): JsonResponse
    {
        $conditions = $discount->conditions()->active()->byPriority('desc')->with('translations')->get();

        return response()->json(['conditions' => $conditions->map(function ($condition) {
            return ['id' => $condition->id, 'type' => $condition->type, 'type_label' => $condition->getTypeLabel(), 'operator' => $condition->operator, 'operator_label' => $condition->getOperatorLabel(), 'value' => $condition->value, 'priority' => $condition->priority, 'position' => $condition->position, 'description' => $condition->human_readable_condition, 'name' => $condition->translated_name];
        })]);
    }

    /**
     * Handle operatorsForType functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/operators-for-type',
        operationId: 'listDiscountConditionOperatorsForType',
        summary: 'List operators compatible with a discount condition type.',
        tags: ['Discount Conditions'],
        parameters: [
            new OA\QueryParameter(
                name: 'type',
                description: 'Discount condition type key.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Operators returned (may be empty when no type supplied).',
                content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionOperatorResponse')
            ),
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

    /**
     * Handle statistics functionality with proper error handling.
     */
    #[OA\Get(
        path: '/discount-conditions/api/statistics',
        operationId: 'getDiscountConditionStatistics',
        summary: 'Return aggregate statistics for discount conditions.',
        tags: ['Discount Conditions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistics payload.',
                content: new OA\JsonContent(ref: '#/components/schemas/DiscountConditionStatisticsResponse')
            ),
        ]
    )]
    public function statistics(): JsonResponse
    {
        $stats = ['total' => DiscountCondition::count(), 'active' => DiscountCondition::where('is_active', true)->count(), 'inactive' => DiscountCondition::where('is_active', false)->count(), 'by_type' => DiscountCondition::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(), 'by_operator' => DiscountCondition::selectRaw('operator, COUNT(*) as count')->groupBy('operator')->pluck('count', 'operator')->toArray()];

        return response()->json($stats);
    }
}
