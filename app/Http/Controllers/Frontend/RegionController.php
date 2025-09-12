<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class RegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Region::with(['country', 'zone', 'parent', 'translations'])
            ->enabled()
            ->ordered()
            ->when($request->filled('search'), function ($query) use ($request) {
                return $query->search($request->search);
            })
            ->when($request->filled('country'), function ($query) use ($request) {
                return $query->byCountry($request->country);
            })
            ->when($request->filled('zone'), function ($query) use ($request) {
                return $query->byZone($request->zone);
            })
            ->when($request->filled('level'), function ($query) use ($request) {
                return $query->byLevel($request->level);
            })
            ->when($request->filled('parent'), function ($query) use ($request) {
                return $query->where('parent_id', $request->parent);
            })
            ->paginate(20);

        return view('frontend.regions.index', compact('regions'));
    }

    public function show(Region $region): View
    {
        $region->load(['country', 'zone', 'parent', 'children', 'cities', 'translations']);

        return view('frontend.regions.show', compact('region'));
    }

    public function children(Region $region): JsonResponse
    {
        $children = $region->children()
            ->enabled()
            ->ordered()
            ->with(['country', 'zone', 'translations'])
            ->get();

        return response()->json([
            'children' => $children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'name' => $child->translated_name,
                    'code' => $child->code,
                    'level' => $child->level,
                    'has_children' => $child->children()->count() > 0,
                    'cities_count' => $child->cities()->count(),
                ];
            }),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['regions' => []]);
        }

        $regions = Region::with(['country', 'zone', 'translations'])
            ->enabled()
            ->search($query)
            ->ordered()
            ->limit(10)
            ->get();

        return response()->json([
            'regions' => $regions->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->translated_name,
                    'code' => $region->code,
                    'country' => $region->country?->name,
                    'zone' => $region->zone?->name,
                    'level' => $region->level,
                    'full_path' => $region->full_path,
                ];
            }),
        ]);
    }

    public function byCountry(Request $request): JsonResponse
    {
        $countryId = $request->get('country_id');

        if (! $countryId) {
            return response()->json(['regions' => []]);
        }

        $regions = Region::with(['translations'])
            ->enabled()
            ->byCountry($countryId)
            ->ordered()
            ->get();

        return response()->json([
            'regions' => $regions->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->translated_name,
                    'code' => $region->code,
                    'level' => $region->level,
                    'has_children' => $region->children()->count() > 0,
                ];
            }),
        ]);
    }

    public function byZone(Request $request): JsonResponse
    {
        $zoneId = $request->get('zone_id');

        if (! $zoneId) {
            return response()->json(['regions' => []]);
        }

        $regions = Region::with(['translations'])
            ->enabled()
            ->byZone($zoneId)
            ->ordered()
            ->get();

        return response()->json([
            'regions' => $regions->map(function ($region) {
                return [
                    'id' => $region->id,
                    'name' => $region->translated_name,
                    'code' => $region->code,
                    'level' => $region->level,
                    'has_children' => $region->children()->count() > 0,
                ];
            }),
        ]);
    }

    public function hierarchy(): JsonResponse
    {
        $regions = Region::with(['children', 'translations'])
            ->enabled()
            ->root()
            ->ordered()
            ->get();

        return response()->json([
            'hierarchy' => $regions->map(function ($region) {
                return $this->buildRegionTree($region);
            }),
        ]);
    }

    private function buildRegionTree(Region $region): array
    {
        $children = $region->children()
            ->enabled()
            ->ordered()
            ->get()
            ->map(function ($child) {
                return $this->buildRegionTree($child);
            });

        return [
            'id' => $region->id,
            'name' => $region->translated_name,
            'code' => $region->code,
            'level' => $region->level,
            'children' => $children,
        ];
    }

    public function stats(Region $region): JsonResponse
    {
        $region->load(['cities', 'addresses', 'users', 'orders', 'customers', 'warehouses', 'stores']);

        return response()->json([
            'stats' => $region->stats,
            'hierarchy' => [
                'is_root' => $region->is_root,
                'is_leaf' => $region->is_leaf,
                'depth' => $region->depth,
                'breadcrumb' => $region->breadcrumb_string,
            ],
        ]);
    }
}
