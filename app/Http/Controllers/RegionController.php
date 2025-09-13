<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class RegionController extends Controller
{
    public function index(Request $request): View
    {
        $regions = Region::query()
            ->with(['country', 'zone', 'parent', 'children'])
            ->when($request->has('country_id'), fn ($query) => $query->where('country_id', $request->get('country_id')))
            ->when($request->has('zone_id'), fn ($query) => $query->where('zone_id', $request->get('zone_id')))
            ->when($request->has('level'), fn ($query) => $query->where('level', $request->get('level')))
            ->when($request->has('parent_id'), fn ($query) => $query->where('parent_id', $request->get('parent_id')))
            ->when($request->has('is_enabled'), fn ($query) => $query->where('is_enabled', $request->boolean('is_enabled')))
            ->when($request->has('search'), fn ($query) => $query->search($request->get('search')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24);

        $countries = \App\Models\Country::where('is_active', true)->orderBy('name')->get(['id', 'name', 'cca2']);
        $zones = \App\Models\Zone::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']);
        $levels = [
            0 => 'Root',
            1 => 'State/Province',
            2 => 'County',
            3 => 'District',
            4 => 'Municipality',
            5 => 'Village',
        ];

        return view('regions.index', compact('regions', 'countries', 'zones', 'levels'));
    }

    public function show(Region $region): View
    {
        $region->load([
            'translations',
            'country.translations',
            'zone',
            'parent.translations',
            'children' => function ($query) {
                $query->where('is_enabled', true)->orderBy('sort_order')->orderBy('name');
            },
            'cities' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')->limit(10);
            },
        ]);

        // Get related regions in the same country
        $relatedRegions = Region::query()
            ->where('country_id', $region->country_id)
            ->where('id', '!=', $region->id)
            ->where('level', $region->level)
            ->where('is_enabled', true)
            ->limit(6)
            ->get();

        return view('regions.show', compact('region', 'relatedRegions'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $regions = Region::query()
            ->with(['country', 'zone'])
            ->where('is_enabled', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'code', 'level', 'country_id', 'zone_id']);

        return response()->json($regions);
    }

    public function byCountry(int $countryId)
    {
        $regions = Region::query()
            ->where('country_id', $countryId)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'parent_id']);

        return response()->json($regions);
    }

    public function byZone(int $zoneId)
    {
        $regions = Region::query()
            ->where('zone_id', $zoneId)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'country_id']);

        return response()->json($regions);
    }

    public function byLevel(int $level)
    {
        $regions = Region::query()
            ->where('level', $level)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'country_id', 'parent_id']);

        return response()->json($regions);
    }

    public function children(int $regionId)
    {
        $regions = Region::query()
            ->where('parent_id', $regionId)
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level']);

        return response()->json($regions);
    }

    public function statistics()
    {
        $stats = [
            'total_regions' => Region::count(),
            'enabled_regions' => Region::where('is_enabled', true)->count(),
            'default_regions' => Region::where('is_default', true)->count(),
            'regions_with_cities' => Region::has('cities')->count(),
            'by_country' => Region::selectRaw('country_id, COUNT(*) as count')
                ->whereNotNull('country_id')
                ->with('country:id,name')
                ->groupBy('country_id')
                ->orderBy('count', 'desc')
                ->get(),
            'by_level' => Region::selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->orderBy('level')
                ->get(),
            'by_zone' => Region::selectRaw('zone_id, COUNT(*) as count')
                ->whereNotNull('zone_id')
                ->with('zone:id,name')
                ->groupBy('zone_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    public function api(Request $request)
    {
        $regions = Region::query()
            ->with(['country', 'zone'])
            ->where('is_enabled', true)
            ->when($request->has('search'), fn ($query) => $query->search($request->get('search')))
            ->when($request->has('country_id'), fn ($query) => $query->where('country_id', $request->get('country_id')))
            ->when($request->has('level'), fn ($query) => $query->where('level', $request->get('level')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'level', 'country_id', 'zone_id', 'parent_id']);

        return response()->json([
            'regions' => $regions,
            'total' => $regions->count()
        ]);
    }
}
