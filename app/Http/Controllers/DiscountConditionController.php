<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\DiscountCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * DiscountConditionController
 *
 * HTTP controller handling DiscountConditionController related web requests, responses, and business logic with proper validation and error handling.
 */
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
    public function statistics(): JsonResponse
    {
        $stats = ['total' => DiscountCondition::count(), 'active' => DiscountCondition::where('is_active', true)->count(), 'inactive' => DiscountCondition::where('is_active', false)->count(), 'by_type' => DiscountCondition::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray(), 'by_operator' => DiscountCondition::selectRaw('operator, COUNT(*) as count')->groupBy('operator')->pluck('count', 'operator')->toArray()];

        return response()->json($stats);
    }
}
