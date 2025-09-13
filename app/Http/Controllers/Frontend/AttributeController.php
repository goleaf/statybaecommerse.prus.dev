<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AttributeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attribute::enabled()
            ->visible()
            ->with(['values' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order');

        // Filter by type if specified
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by group if specified
        if ($request->has('group') && $request->group) {
            $query->where('group_name', $request->group);
        }

        // Filter by category if specified
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $attributes = $query->paginate(20);

        // Get filter options
        $types = Attribute::enabled()
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [$type => __('attributes.'.$type)]);

        $groups = Attribute::enabled()
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->mapWithKeys(fn ($group) => [$group => __('attributes.'.$group)]);

        return view('attributes.index', compact('attributes', 'types', 'groups'));
    }

    public function show(Attribute $attribute): View
    {
        $attribute->load([
            'values' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order');
            },
            'products' => function ($query) {
                $query->where('is_enabled', true)
                    ->where('is_published', true)
                    ->with(['media', 'brand', 'category'])
                    ->orderBy('name');
            },
        ]);

        // Get related attributes from the same group
        $relatedAttributes = collect();
        if ($attribute->group_name) {
            $relatedAttributes = Attribute::enabled()
                ->visible()
                ->where('group_name', $attribute->group_name)
                ->where('id', '!=', $attribute->id)
                ->with(['values' => function ($query) {
                    $query->where('is_enabled', true)->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->limit(5)
                ->get();
        }

        return view('attributes.show', compact('attribute', 'relatedAttributes'));
    }

    public function filter(Request $request)
    {
        $query = Product::enabled()
            ->published()
            ->with(['media', 'brand', 'category', 'attributes']);

        // Apply attribute filters
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $attributeId => $values) {
                if (! empty($values)) {
                    $query->whereHas('attributes', function ($q) use ($attributeId, $values) {
                        $q->where('attribute_id', $attributeId)
                            ->whereIn('attribute_value_id', (array) $values);
                    });
                }
            }
        }

        // Apply price range filter
        if ($request->has('price_min') && $request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        // Apply search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }

        $products = $query->paginate(20);

        // Get available filter options
        $filterableAttributes = Attribute::enabled()
            ->filterable()
            ->with(['values' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('products.filtered', compact('products', 'filterableAttributes'));
    }

    public function getAttributeValues(Request $request)
    {
        $attributeId = $request->get('attribute_id');

        if (! $attributeId) {
            return response()->json([]);
        }

        $attribute = Attribute::find($attributeId);

        if (! $attribute) {
            return response()->json([]);
        }

        $values = $attribute->enabledValues()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($value) {
                return [
                    'id' => $value->id,
                    'value' => $value->value,
                    'display_value' => $value->display_value ?: $value->value,
                    'color' => $value->color,
                ];
            });

        return response()->json($values);
    }
}
