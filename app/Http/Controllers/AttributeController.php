<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AttributeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Attribute::withTranslations()
            ->enabled()
            ->ordered();

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }

        if ($request->filled('group')) {
            $query->byGroup($request->get('group'));
        }

        if ($request->filled('category')) {
            $query->byCategory((int) $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $attributes = $query->paginate(12);

        return view('attributes.index', compact('attributes'));
    }

    public function show(Attribute $attribute): View
    {
        $attribute->load(['values' => function ($query) {
            $query->enabled()->ordered();
        }]);

        $relatedAttributes = Attribute::withTranslations()
            ->enabled()
            ->where('id', '!=', $attribute->id)
            ->where('group_name', $attribute->group_name)
            ->limit(4)
            ->get();

        return view('attributes.show', compact('attribute', 'relatedAttributes'));
    }

    // API Endpoints
    public function api(Request $request): JsonResponse
    {
        $query = Attribute::withTranslations()->enabled();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $attributes = $query->limit(20)->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslatedName(),
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->getTranslatedDescription(),
                    'group_name' => $attribute->group_name,
                    'is_required' => $attribute->is_required,
                    'is_filterable' => $attribute->is_filterable,
                    'is_searchable' => $attribute->is_searchable,
                    'values_count' => $attribute->getValuesCount(),
                    'usage_count' => $attribute->getUsageCount(),
                ];
            }),
        ]);
    }

    public function byType(string $type): JsonResponse
    {
        $attributes = Attribute::withTranslations()
            ->enabled()
            ->byType($type)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslatedName(),
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->getTranslatedDescription(),
                    'group_name' => $attribute->group_name,
                    'values_count' => $attribute->getValuesCount(),
                    'usage_count' => $attribute->getUsageCount(),
                ];
            }),
        ]);
    }

    public function byGroup(string $group): JsonResponse
    {
        $attributes = Attribute::withTranslations()
            ->enabled()
            ->byGroup($group)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslatedName(),
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->getTranslatedDescription(),
                    'group_name' => $attribute->group_name,
                    'values_count' => $attribute->getValuesCount(),
                    'usage_count' => $attribute->getUsageCount(),
                ];
            }),
        ]);
    }

    public function filterable(): JsonResponse
    {
        $attributes = Attribute::withTranslations()
            ->enabled()
            ->filterable()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslatedName(),
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->getTranslatedDescription(),
                    'group_name' => $attribute->group_name,
                    'values_count' => $attribute->getValuesCount(),
                ];
            }),
        ]);
    }

    public function required(): JsonResponse
    {
        $attributes = Attribute::withTranslations()
            ->enabled()
            ->required()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->getTranslatedName(),
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->getTranslatedDescription(),
                    'group_name' => $attribute->group_name,
                    'values_count' => $attribute->getValuesCount(),
                    'usage_count' => $attribute->getUsageCount(),
                ];
            }),
        ]);
    }

    public function statistics(): JsonResponse
    {
        $totalAttributes = Attribute::count();
        $enabledAttributes = Attribute::enabled()->count();
        $requiredAttributes = Attribute::required()->count();
        $filterableAttributes = Attribute::filterable()->count();
        $searchableAttributes = Attribute::searchable()->count();
        $attributesWithValues = Attribute::has('values')->count();

        return response()->json([
            'total_attributes' => $totalAttributes,
            'enabled_attributes' => $enabledAttributes,
            'required_attributes' => $requiredAttributes,
            'filterable_attributes' => $filterableAttributes,
            'searchable_attributes' => $searchableAttributes,
            'attributes_with_values' => $attributesWithValues,
            'attributes_without_values' => $totalAttributes - $attributesWithValues,
        ]);
    }

    public function values(Attribute $attribute): JsonResponse
    {
        $values = $attribute->enabledValues()->paginate(20);

        return response()->json([
            'attribute' => [
                'id' => $attribute->id,
                'name' => $attribute->getTranslatedName(),
                'slug' => $attribute->slug,
                'type' => $attribute->type,
                'description' => $attribute->getTranslatedDescription(),
                'group_name' => $attribute->group_name,
            ],
            'values' => $values->items(),
            'pagination' => [
                'current_page' => $values->currentPage(),
                'last_page' => $values->lastPage(),
                'per_page' => $values->perPage(),
                'total' => $values->total(),
            ],
        ]);
    }
}
