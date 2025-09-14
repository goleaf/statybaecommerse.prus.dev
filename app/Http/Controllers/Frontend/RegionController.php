<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
/**
 * RegionController
 * 
 * HTTP controller handling RegionController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class RegionController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $regions = Region::with(['country', 'zone', 'parent', 'translations'])->enabled()->ordered()->when($request->filled('search'), function ($query) use ($request) {
            return $query->search($request->search);
        })->when($request->filled('country'), function ($query) use ($request) {
            return $query->byCountry($request->country);
        })->when($request->filled('zone'), function ($query) use ($request) {
            return $query->byZone($request->zone);
        })->when($request->filled('level'), function ($query) use ($request) {
            return $query->byLevel($request->level);
        })->when($request->filled('parent'), function ($query) use ($request) {
            return $query->where('parent_id', $request->parent);
        })->paginate(20);
        return view('regions.index', compact('regions'));
    }
    /**
     * Display the specified resource with related data.
     * @param Region $region
     * @return View
     */
    public function show(Region $region): View
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$region->relationLoaded('country') || !$region->relationLoaded('zone') || !$region->relationLoaded('parent') || !$region->relationLoaded('children') || !$region->relationLoaded('cities') || !$region->relationLoaded('translations')) {
            $region->load(['country', 'zone', 'parent', 'children', 'cities', 'translations']);
        }
        return view('regions.show', compact('region'));
    }
    /**
     * Handle children functionality with proper error handling.
     * @param Region $region
     * @return JsonResponse
     */
    public function children(Region $region): JsonResponse
    {
        $children = $region->children()->enabled()->ordered()->with(['country', 'zone', 'translations'])->get();
        return response()->json(['children' => $children->map(function ($child) {
            return ['id' => $child->id, 'name' => $child->translated_name, 'code' => $child->code, 'level' => $child->level, 'has_children' => $child->children()->count() > 0, 'cities_count' => $child->cities()->count()];
        })]);
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json(['regions' => []]);
        }
        $regions = Region::with(['country', 'zone', 'translations'])->enabled()->search($query)->ordered()->limit(10)->get();
        return response()->json(['regions' => $regions->map(function ($region) {
            return ['id' => $region->id, 'name' => $region->translated_name, 'code' => $region->code, 'country' => $region->country?->name, 'zone' => $region->zone?->name, 'level' => $region->level, 'full_path' => $region->full_path];
        })]);
    }
    /**
     * Handle byCountry functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function byCountry(Request $request): JsonResponse
    {
        $countryId = $request->get('country_id');
        if (!$countryId) {
            return response()->json(['regions' => []]);
        }
        $regions = Region::with(['translations'])->enabled()->byCountry($countryId)->ordered()->get();
        return response()->json(['regions' => $regions->map(function ($region) {
            return ['id' => $region->id, 'name' => $region->translated_name, 'code' => $region->code, 'level' => $region->level, 'has_children' => $region->children()->count() > 0];
        })]);
    }
    /**
     * Handle byZone functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function byZone(Request $request): JsonResponse
    {
        $zoneId = $request->get('zone_id');
        if (!$zoneId) {
            return response()->json(['regions' => []]);
        }
        $regions = Region::with(['translations'])->enabled()->byZone($zoneId)->ordered()->get();
        return response()->json(['regions' => $regions->map(function ($region) {
            return ['id' => $region->id, 'name' => $region->translated_name, 'code' => $region->code, 'level' => $region->level, 'has_children' => $region->children()->count() > 0];
        })]);
    }
    /**
     * Handle hierarchy functionality with proper error handling.
     * @return JsonResponse
     */
    public function hierarchy(): JsonResponse
    {
        $regions = Region::with(['children', 'translations'])->enabled()->root()->ordered()->get();
        return response()->json(['hierarchy' => $regions->map(function ($region) {
            return $this->buildRegionTree($region);
        })]);
    }
    /**
     * Handle buildRegionTree functionality with proper error handling.
     * @param Region $region
     * @return array
     */
    private function buildRegionTree(Region $region): array
    {
        $children = $region->children()->enabled()->ordered()->get()->map(function ($child) {
            return $this->buildRegionTree($child);
        });
        return ['id' => $region->id, 'name' => $region->translated_name, 'code' => $region->code, 'level' => $region->level, 'children' => $children];
    }
    /**
     * Handle stats functionality with proper error handling.
     * @param Region $region
     * @return JsonResponse
     */
    public function stats(Region $region): JsonResponse
    {
        $region->load(['cities', 'addresses', 'users', 'orders', 'customers', 'warehouses', 'stores']);
        return response()->json(['stats' => $region->stats, 'hierarchy' => ['is_root' => $region->is_root, 'is_leaf' => $region->is_leaf, 'depth' => $region->depth, 'breadcrumb' => $region->breadcrumb_string]]);
    }
}