<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * LocationController
 * 
 * HTTP controller handling web requests and responses.
 */
class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $locations = Location::query()
            ->enabled()
            ->with(['country', 'inventories'])
            ->when($request->has('type'), fn ($query) => $query->where('type', $request->get('type')))
            ->when($request->has('country'), fn ($query) => $query->where('country_code', $request->get('country')))
            ->when($request->has('city'), fn ($query) => $query->where('city', $request->get('city')))
            ->when($request->has('has_coordinates'), fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($request->has('has_opening_hours'), fn ($query) => $query->whereNotNull('opening_hours'))
            ->when($request->has('is_open_now'), fn ($query) => $query->where('is_enabled', true))
            ->when($request->has('search'), fn ($query) => $query->where(function ($q) use ($request) {
                $search = $request->get('search');
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address_line_1', 'like', "%{$search}%");
            }))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24);

        $types = Location::distinct()->pluck('type')->filter()->sort()->values();
        $countries = Location::distinct()->pluck('country_code')->filter()->sort()->values();
        $cities = Location::distinct()->pluck('city')->filter()->sort()->values();

        return view('locations.index', compact('locations', 'types', 'countries', 'cities'));
    }

    public function show(Location $location): View
    {
        $location->load([
            'translations',
            'country',
            'inventories' => function ($query) {
                $query->with('product')->latest()->limit(10);
            },
        ]);

        // Get related locations of the same type in the same city
        $relatedLocations = Location::query()
            ->where('type', $location->type)
            ->where('city', $location->city)
            ->where('id', '!=', $location->id)
            ->enabled()
            ->limit(6)
            ->get();

        return view('locations.show', compact('location', 'relatedLocations'));
    }

    public function api(Request $request): JsonResponse
    {
        $locations = Location::query()
            ->enabled()
            ->when($request->has('search'), fn ($query) => $query->where(function ($q) use ($request) {
                $search = $request->get('search');
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            }))
            ->when($request->has('type'), fn ($query) => $query->where('type', $request->get('type')))
            ->when($request->has('country'), fn ($query) => $query->where('country_code', $request->get('country')))
            ->when($request->has('city'), fn ($query) => $query->where('city', $request->get('city')))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'type', 'city', 'country_code', 'latitude', 'longitude']);

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count()
        ]);
    }

    public function byType(Request $request, string $type): JsonResponse
    {
        $locations = Location::query()
            ->where('type', $type)
            ->enabled()
            ->with(['country'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count()
        ]);
    }

    public function byCountry(Request $request, string $countryCode): JsonResponse
    {
        $locations = Location::query()
            ->where('country_code', $countryCode)
            ->enabled()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count()
        ]);
    }

    public function byCity(Request $request, string $city): JsonResponse
    {
        $locations = Location::query()
            ->where('city', 'like', "%{$city}%")
            ->enabled()
            ->with(['country'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count()
        ]);
    }

    public function nearby(Request $request): JsonResponse
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $radius = $request->get('radius', 10); // Default 10km radius

        if (!$latitude || !$longitude) {
            return response()->json(['error' => 'Latitude and longitude are required'], 400);
        }

        $locations = Location::query()
            ->enabled()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->limit(20)
            ->get();

        return response()->json([
            'locations' => $locations,
            'total' => $locations->count(),
            'center' => ['latitude' => $latitude, 'longitude' => $longitude],
            'radius' => $radius
        ]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json([
            'total_locations' => Location::count(),
            'enabled_locations' => Location::enabled()->count(),
            'disabled_locations' => Location::where('is_enabled', false)->count(),
            'default_locations' => Location::default()->count(),
            'by_type' => Location::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->mapWithKeys(function ($item) {
                    $typeLabel = match ($item->type) {
                        'warehouse' => 'Warehouse',
                        'store' => 'Store',
                        'office' => 'Office',
                        'pickup_point' => 'Pickup Point',
                        'other' => 'Other',
                        default => $item->type
                    };
                    return [$typeLabel => $item->count];
                }),
            'by_country' => Location::selectRaw('country_code, COUNT(*) as count')
                ->groupBy('country_code')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->country_code => $item->count];
                }),
            'with_coordinates' => Location::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'with_opening_hours' => Location::whereNotNull('opening_hours')->count(),
        ]);
    }
}