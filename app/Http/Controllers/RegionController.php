<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * RegionController
 * 
 * HTTP controller handling web requests and responses.
 */
class RegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Region::query()
            ->enabled()
            ->with(['country', 'parent', 'cities'])
            ->when($request->has('country'), fn ($query) => $query->where('country_id', $request->get('country')))
            ->when($request->has('level'), fn ($query) => $query->where('level', $request->get('level')))
            ->when($request->has('parent'), fn ($query) => $query->where('parent_id', $request->get('parent')))
            ->when($request->has('has_children'), fn ($query) => $query->has('children'))
            ->when($request->has('has_cities'), fn ($query) => $query->has('cities'))
            ->when($request->has('search'), fn ($query) => $query->search($request->get('search')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->skipWhile(function ($region) {
                // Skip regions that are not properly configured for display
                return empty($region->name) || 
                       !$region->is_enabled ||
                       empty($region->level) ||
                       empty($region->country_id);
            })
            ->paginate(24);

        $countries = Region::with('country')
            ->distinct()
            ->get()
            ->pluck('country.name', 'country.id')
            ->filter()
            ->sort()
            ->values();

        $levels = Region::distinct()->pluck('level')->filter()->sort()->values();
        
        $parents = Region::enabled()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('regions.index', compact('regions', 'countries', 'levels', 'parents'));
    }

    public function show(Region $region): View
    {
        $region->load([
            'translations',
            'country',
            'parent',
            'children' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order')->orderBy('name');
            },
            'cities' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            },
            'addresses' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        // Get related regions in the same country
        $relatedRegions = Region::query()
            ->where('country_id', $region->country_id)
            ->where('id', '!=', $region->id)
            ->where('level', $region->level)
            ->enabled()
            ->limit(6)
            ->get()
            ->skipWhile(function ($relatedRegion) {
                // Skip related regions that are not properly configured for display
                return empty($relatedRegion->name) || 
                       !$relatedRegion->is_enabled ||
                       empty($relatedRegion->level) ||
                       empty($relatedRegion->country_id);
            });

        return view('regions.show', compact('region', 'relatedRegions'));
    }

    public function api(Request $request): JsonResponse
    {
        $regions = Region::query()
            ->enabled()
            ->when($request->has('search'), fn ($query) => $query->search($request->get('search')))
            ->when($request->has('country'), fn ($query) => $query->where('country_id', $request->get('country')))
            ->when($request->has('level'), fn ($query) => $query->where('level', $request->get('level')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'country_id', 'parent_id'])
            ->skipWhile(function ($region) {
                // Skip regions that are not properly configured for API response
                return empty($region->name) || 
                       !$region->is_enabled ||
                       empty($region->level) ||
                       empty($region->country_id);
            });

        return response()->json([
            'regions' => $regions,
            'total' => $regions->count()
        ]);
    }

    public function byCountry(Request $request, string $countryId): JsonResponse
    {
        $regions = Region::query()
            ->where('country_id', $countryId)
            ->enabled()
            ->with(['children' => function ($query) {
                $query->enabled()->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'regions' => $regions,
            'total' => $regions->count()
        ]);
    }

    public function byLevel(Request $request, int $level): JsonResponse
    {
        $regions = Region::query()
            ->where('level', $level)
            ->enabled()
            ->with(['country', 'parent'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'regions' => $regions,
            'total' => $regions->count()
        ]);
    }

    public function hierarchy(Request $request): JsonResponse
    {
        $regions = Region::query()
            ->enabled()
            ->root()
            ->with(['children' => function ($query) {
                $query->enabled()->with(['children' => function ($subQuery) {
                    $subQuery->enabled();
                }]);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'hierarchy' => $regions,
            'total' => $regions->count()
        ]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json([
            'total_regions' => Region::count(),
            'enabled_regions' => Region::enabled()->count(),
            'default_regions' => Region::default()->count(),
            'root_regions' => Region::root()->count(),
            'by_level' => Region::selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->orderBy('level')
                ->get()
                ->mapWithKeys(function ($item) {
                    $levelName = match ($item->level) {
                        0 => 'Root',
                        1 => 'State/Province',
                        2 => 'County',
                        3 => 'District',
                        4 => 'Municipality',
                        5 => 'Village',
                        default => "Level {$item->level}"
                    };
                    return [$levelName => $item->count];
                }),
            'by_country' => Region::with('country')
                ->selectRaw('country_id, COUNT(*) as count')
                ->groupBy('country_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    $countryName = $item->country?->name ?? 'Unknown';
                    return [$countryName => $item->count];
                }),
        ]);
    }
}