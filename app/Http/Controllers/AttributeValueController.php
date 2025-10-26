<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Services\PaginationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AttributeValueController
 *
 * HTTP controller handling AttributeValueController related web requests, responses, and business logic with proper validation and error handling.
 */
final class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $query = AttributeValue::with(['attribute.translations', 'translations'])
            ->withCount(['products', 'variants'])
            ->enabled()
            ->ordered()
            ->whereNotNull('value')
            ->whereHas('attribute');
        // Filter by attribute if provided
        if ($request->has('attribute_id') && $request->attribute_id) {
            $query->forAttribute((int) $request->attribute_id);
        }
        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('value', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhereHas('attribute', function ($attrQuery) use ($search): void {
                    $attrQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        // Filter by color
        if ($request->has('with_color') && $request->with_color) {
            $query->withColor();
        }
        // Filter by required
        if ($request->has('required') && $request->required) {
            $query->required();
        }
        // Filter by default
        if ($request->has('default') && $request->default) {
            $query->default();
        }
        $attributeValues = PaginationService::paginateWithOnEachSide($query, 20);
        $attributeValues->appends($request->query());
        $attributes = Attribute::enabled()->ordered()->get();

        return view('attribute-values.index', ['attributeValues' => $attributeValues, 'attributes' => $attributes]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(AttributeValue $attributeValue): View
    {
        $attributeValue->load(['attribute', 'products', 'variants', 'translations']);

        return view('attribute-values.show', ['attributeValue' => $attributeValue]);
    }

    /**
     * Handle byAttribute functionality with proper error handling.
     */
    public function byAttribute(Attribute $attribute): View
    {
        $attributeValuesQuery = $attribute->values()
            ->with(['translations'])
            ->withCount(['products', 'variants'])
            ->whereNotNull('value');
        $attributeValues = PaginationService::paginateWithOnEachSide($attributeValuesQuery, 20);
        $attributeValues->appends(request()->query());

        return view('attribute-values.by-attribute', ['attribute' => $attribute, 'attributeValues' => $attributeValues]);
    }

    /**
     * Handle api functionality with proper error handling.
     */
    public function api(Request $request): JsonResponse
    {
        $query = AttributeValue::with(['attribute', 'translations'])->enabled()->ordered();
        // Filter by attribute if provided
        if ($request->has('attribute_id') && $request->attribute_id) {
            $query->forAttribute((int) $request->attribute_id);
        }
        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('value', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhereHas('attribute', function ($attrQuery) use ($search): void {
                    $attrQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        $attributeValues = $query->get()->skipWhile(fn ($attributeValue): bool => // Skip attribute values that are not properly configured for API response
            empty($attributeValue->value) || ! $attributeValue->is_enabled || empty($attributeValue->attribute) || empty($attributeValue->attribute_id))->map(fn ($attributeValue): array => ['id' => $attributeValue->id, 'value' => $attributeValue->getDisplayValue(), 'description' => $attributeValue->getDisplayDescription(), 'color_code' => $attributeValue->color_code, 'attribute' => ['id' => $attributeValue->attribute->id, 'name' => $attributeValue->attribute->getDisplayName()], 'products_count' => $attributeValue->products()->count(), 'variants_count' => $attributeValue->variants()->count()]);

        return response()->json(['data' => $attributeValues, 'meta' => ['total' => $attributeValues->count()]]);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $attributeId = $request->get('attribute_id');
        if (empty($query)) {
            return response()->json(['data' => []]);
        }
        $attributeValues = AttributeValue::with(['attribute', 'translations'])->enabled()->where('value', 'like', "%{$query}%")->when($attributeId, fn ($q) => $q->forAttribute((int) $attributeId))->limit(10)->get()->map(fn ($attributeValue): array => ['id' => $attributeValue->id, 'value' => $attributeValue->getDisplayValue(), 'attribute_name' => $attributeValue->attribute->getDisplayName()]);

        return response()->json(['data' => $attributeValues]);
    }
}
