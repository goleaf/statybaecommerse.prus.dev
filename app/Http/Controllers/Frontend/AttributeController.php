<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * AttributeController
 * 
 * HTTP controller handling web requests and responses.
 */
class AttributeController extends Controller
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
                $query->where('is_visible', true)
                    ->whereNotNull('published_at')
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
        $query = Product::visible()
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

    public function getAttributeStatistics(Request $request)
    {
        $attributeId = $request->get('attribute_id');

        if (!$attributeId) {
            return response()->json(['error' => 'Attribute ID is required']);
        }

        $attribute = Attribute::find($attributeId);

        if (!$attribute) {
            return response()->json(['error' => 'Attribute not found']);
        }

        $statistics = $attribute->getStatistics();

        return response()->json($statistics);
    }

    public function getAttributeGroups(Request $request)
    {
        $groups = Attribute::enabled()
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name')
            ->mapWithKeys(fn ($group) => [
                $group => [
                    'name' => $group,
                    'label' => __('attributes.' . $group),
                    'count' => Attribute::where('group_name', $group)->count(),
                    'enabled_count' => Attribute::where('group_name', $group)->where('is_enabled', true)->count(),
                ]
            ]);

        return response()->json($groups);
    }

    public function getAttributeTypes(Request $request)
    {
        $types = Attribute::enabled()
            ->distinct()
            ->pluck('type')
            ->mapWithKeys(fn ($type) => [
                $type => [
                    'name' => $type,
                    'label' => __('attributes.' . $type),
                    'count' => Attribute::where('type', $type)->count(),
                    'enabled_count' => Attribute::where('type', $type)->where('is_enabled', true)->count(),
                    'icon' => match ($type) {
                        'text' => 'heroicon-o-document-text',
                        'number' => 'heroicon-o-calculator',
                        'boolean' => 'heroicon-o-check-circle',
                        'select' => 'heroicon-o-list-bullet',
                        'multiselect' => 'heroicon-o-squares-2x2',
                        'color' => 'heroicon-o-swatch',
                        'date' => 'heroicon-o-calendar',
                        'textarea' => 'heroicon-o-document',
                        'file' => 'heroicon-o-paper-clip',
                        'image' => 'heroicon-o-photo',
                        default => 'heroicon-o-adjustments-horizontal',
                    },
                    'color' => match ($type) {
                        'text' => 'gray',
                        'number' => 'blue',
                        'boolean' => 'green',
                        'select' => 'yellow',
                        'multiselect' => 'orange',
                        'color' => 'purple',
                        'date' => 'red',
                        'textarea' => 'indigo',
                        'file' => 'pink',
                        'image' => 'rose',
                        default => 'gray',
                    },
                ]
            ]);

        return response()->json($types);
    }

    public function searchAttributes(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type');
        $group = $request->get('group');
        $category = $request->get('category');

        $attributes = Attribute::enabled()
            ->visible()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($group, function ($q) use ($group) {
                $q->where('group_name', $group);
            })
            ->when($category, function ($q) use ($category) {
                $q->where('category_id', $category);
            })
            ->with(['values' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->limit(20)
            ->get()
            ->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'type_label' => __('attributes.' . $attribute->type),
                    'type_icon' => $attribute->type_icon,
                    'type_color' => $attribute->type_color,
                    'group_name' => $attribute->group_name,
                    'description' => $attribute->description,
                    'is_required' => $attribute->is_required,
                    'is_filterable' => $attribute->is_filterable,
                    'is_searchable' => $attribute->is_searchable,
                    'values_count' => $attribute->values->count(),
                    'values' => $attribute->values->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'display_value' => $value->display_value ?: $value->value,
                            'color' => $value->color,
                        ];
                    }),
                ];
            });

        return response()->json($attributes);
    }

    public function getAttributeComparison(Request $request)
    {
        $attributeIds = $request->get('attribute_ids', []);

        if (empty($attributeIds) || count($attributeIds) < 2) {
            return response()->json(['error' => 'At least 2 attribute IDs are required']);
        }

        $attributes = Attribute::whereIn('id', $attributeIds)
            ->with(['values', 'products'])
            ->get()
            ->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'type' => $attribute->type,
                    'type_label' => __('attributes.' . $attribute->type),
                    'group_name' => $attribute->group_name,
                    'statistics' => $attribute->getStatistics(),
                    'values' => $attribute->values->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'display_value' => $value->display_value ?: $value->value,
                            'usage_count' => $value->products()->count(),
                        ];
                    }),
                ];
            });

        return response()->json($attributes);
    }
}
