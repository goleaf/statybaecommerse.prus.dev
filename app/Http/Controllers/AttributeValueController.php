<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * AttributeValueController
 * 
 * HTTP controller handling web requests and responses.
 */
class AttributeValueController extends Controller
{
    public function index(Request $request): View
    {
        $query = AttributeValue::with(['attribute', 'translations'])
            ->enabled()
            ->ordered();

        // Filter by attribute if provided
        if ($request->has('attribute_id') && $request->attribute_id) {
            $query->forAttribute((int) $request->attribute_id);
        }

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('value', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('attribute', function ($attrQuery) use ($search) {
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

        $attributeValues = $query->paginate(20);
        $attributes = Attribute::enabled()->ordered()->get();

        return view('attribute-values.index', compact('attributeValues', 'attributes'));
    }

    public function show(AttributeValue $attributeValue): View
    {
        $attributeValue->load(['attribute', 'products', 'variants', 'translations']);

        return view('attribute-values.show', compact('attributeValue'));
    }

    public function byAttribute(Attribute $attribute): View
    {
        $attributeValues = $attribute
            ->values()
            ->enabled()
            ->ordered()
            ->with('translations')
            ->paginate(20);

        return view('attribute-values.by-attribute', compact('attribute', 'attributeValues'));
    }

    public function api(Request $request): JsonResponse
    {
        $query = AttributeValue::with(['attribute', 'translations'])
            ->enabled()
            ->ordered();

        // Filter by attribute if provided
        if ($request->has('attribute_id') && $request->attribute_id) {
            $query->forAttribute((int) $request->attribute_id);
        }

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('value', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('attribute', function ($attrQuery) use ($search) {
                        $attrQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $attributeValues = $query->get()->map(function ($attributeValue) {
            return [
                'id' => $attributeValue->id,
                'value' => $attributeValue->getDisplayValue(),
                'description' => $attributeValue->getDisplayDescription(),
                'color_code' => $attributeValue->color_code,
                'attribute' => [
                    'id' => $attributeValue->attribute->id,
                    'name' => $attributeValue->attribute->getDisplayName(),
                ],
                'products_count' => $attributeValue->products()->count(),
                'variants_count' => $attributeValue->variants()->count(),
            ];
        });

        return response()->json([
            'data' => $attributeValues,
            'meta' => [
                'total' => $attributeValues->count(),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $attributeId = $request->get('attribute_id');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $attributeValues = AttributeValue::with(['attribute', 'translations'])
            ->enabled()
            ->where('value', 'like', "%{$query}%")
            ->when($attributeId, function ($q) use ($attributeId) {
                return $q->forAttribute((int) $attributeId);
            })
            ->limit(10)
            ->get()
            ->map(function ($attributeValue) {
                return [
                    'id' => $attributeValue->id,
                    'value' => $attributeValue->getDisplayValue(),
                    'attribute_name' => $attributeValue->attribute->getDisplayName(),
                ];
            });

        return response()->json(['data' => $attributeValues]);
    }
}
