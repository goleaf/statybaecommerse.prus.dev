<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * LocationController
 *
 * HTTP controller handling LocationController related web requests, responses, and business logic with proper validation and error handling.
 */
final class LocationController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(Request $request): View
    {
        $locationsQuery = Location::query()
            ->enabled()
            ->with(['country', 'inventories'])
            ->when($request->has('type'), fn ($query) => $query->where('type', $request->get('type')))
            ->when($request->has('country'), fn ($query) => $query->where('country_code', $request->get('country')))
            ->when($request->has('city'), fn ($query) => $query->where('city', $request->get('city')))
            ->when($request->has('has_coordinates'), fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($request->has('has_opening_hours'), fn ($query) => $query->whereNotNull('opening_hours'))
            ->when($request->has('is_open_now'), fn ($query) => $query->where('is_enabled', true))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address_line_1', 'like', "%{$search}%");
                });
            });

        // Guard against incomplete records before pagination so the paginator always operates on the query builder
        $locations = $locationsQuery
            ->whereNotNull('name')
            ->where('name', '<>', '')
            ->whereNotNull('type')
            ->where('type', '<>', '')
            ->whereNotNull('city')
            ->where('city', '<>', '')
            ->whereNotNull('country_code')
            ->where('country_code', '<>', '')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(24);
        $types = Location::distinct()->pluck('type')->filter()->sort()->values();
        $countries = Location::distinct()->pluck('country_code')->filter()->sort()->values();
        $cities = Location::distinct()->pluck('city')->filter()->sort()->values();

        return view('locations.index', ['locations' => $locations, 'types' => $types, 'countries' => $countries, 'cities' => $cities]);
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Location $location): View
    {
        $location->load(['translations', 'country', 'inventories' => function ($query): void {
            /** @var HasMany<\App\Models\Inventory, Location> $query */
            // Keep the eager-loaded inventories lightweight so the product slider stays responsive.
            $query->with('product')->latest()->limit(10);
        }]);
        // Fetch related locations and immediately filter anything that cannot be rendered safely.
        $relatedLocations = $this->filterDisplayableLocations(
            Location::query()
                ->where('type', $location->type)
                ->where('city', $location->city)
                ->where('id', '!=', $location->id)
                ->enabled()
                ->limit(6)
                ->get()
        );

        return view('locations.show', ['location' => $location, 'relatedLocations' => $relatedLocations]);
    }

    /**
     * Handle api functionality with proper error handling.
     */
    public function api(Request $request): JsonResponse
    {
        $locations = $this->filterDisplayableLocations(
            Location::query()
                ->enabled()
                ->when($request->filled('search'), fn ($query) => $query->where(function ($q) use ($request): void {
                    $search = $request->string('search')->toString();
                    // Perform a simple LIKE search across the human readable columns so the public API stays flexible.
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                }))
                ->when($request->has('type'), fn ($query) => $query->where('type', $request->get('type')))
                ->when($request->has('country'), fn ($query) => $query->where('country_code', $request->get('country')))
                ->when($request->has('city'), fn ($query) => $query->where('city', $request->get('city')))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'type', 'city', 'country_code', 'latitude', 'longitude'])
        );

        return response()->json(['locations' => $locations, 'total' => $locations->count()]);
    }

    /**
     * Handle byType functionality with proper error handling.
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $locations = $this->filterDisplayableLocations(
            Location::query()
                ->where('type', $type)
                ->enabled()
                ->with(['country'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );

        return response()->json(['locations' => $locations, 'total' => $locations->count()]);
    }

    /**
     * Handle byCountry functionality with proper error handling.
     */
    public function byCountry(Request $request, string $countryCode): JsonResponse
    {
        $locations = $this->filterDisplayableLocations(
            Location::query()
                ->where('country_code', $countryCode)
                ->enabled()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );

        return response()->json(['locations' => $locations, 'total' => $locations->count()]);
    }

    /**
     * Handle byCity functionality with proper error handling.
     */
    public function byCity(Request $request, string $city): JsonResponse
    {
        $locations = $this->filterDisplayableLocations(
            Location::query()
                ->where('city', 'like', "%{$city}%")
                ->enabled()
                ->with(['country'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );

        return response()->json(['locations' => $locations, 'total' => $locations->count()]);
    }

    /**
     * Handle nearby functionality with proper error handling.
     */
    public function nearby(Request $request): JsonResponse
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radiusInput = $request->input('radius', 10);
        // Default 10km radius
        if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
            return response()->json(['error' => 'Latitude and longitude are required'], 400);
        }
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            // Provide deterministic error messaging when the coordinates are malformed.
            return response()->json(['error' => 'Latitude and longitude must be numeric'], 422);
        }
        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        $radius = is_numeric($radiusInput) ? (float) $radiusInput : 10.0;
        $locations = $this->filterDisplayableLocations(
            Location::query()
                ->enabled()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$latitude, $longitude, $latitude])
                ->having('distance', '<', $radius)
                ->orderBy('distance')
                ->limit(20)
                ->get()
        );

        return response()->json(['locations' => $locations, 'total' => $locations->count(), 'center' => ['latitude' => $latitude, 'longitude' => $longitude], 'radius' => $radius]);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(): JsonResponse
    {
        $locationsWithoutScopes = Location::withoutGlobalScopes();

        // Cloning keeps every aggregate isolated so we do not accumulate query constraints between calls.
        $totalLocations = (clone $locationsWithoutScopes)->count();
        $enabledLocations = Location::query()->count();
        $disabledLocations = (clone $locationsWithoutScopes)->where('is_enabled', false)->count();
        $defaultLocations = (clone $locationsWithoutScopes)->where('is_default', true)->count();

        $byType = (clone $locationsWithoutScopes)
            ->selectRaw('type, COUNT(*) as aggregate_count')
            ->whereNotNull('type')
            ->where('type', '<>', '')
            ->groupBy('type')
            ->get()
            ->pluck('aggregate_count', 'type')
            ->mapWithKeys(function ($count, $type): array {
                $typeLabel = match ($type) {
                    'warehouse'    => 'Warehouse',
                    'store'        => 'Store',
                    'office'       => 'Office',
                    'pickup_point' => 'Pickup Point',
                    'other'        => 'Other',
                    default        => (string) $type,
                };

                // Cast the aggregate to an integer to prevent JSON numbers becoming strings on some drivers.
                $normalizedCount = is_numeric($count) ? (int) $count : 0;

                return [$typeLabel => $normalizedCount];
            });

        $byCountry = (clone $locationsWithoutScopes)
            ->selectRaw('country_code, COUNT(*) as aggregate_count')
            ->whereNotNull('country_code')
            ->where('country_code', '<>', '')
            ->groupBy('country_code')
            ->get()
            ->pluck('aggregate_count', 'country_code')
            ->mapWithKeys(function ($count, $countryCode): array {
                $normalizedCount = is_numeric($count) ? (int) $count : 0;

                return [(string) $countryCode => $normalizedCount];
            });

        $withCoordinates = (clone $locationsWithoutScopes)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();

        $withOpeningHours = (clone $locationsWithoutScopes)
            ->whereNotNull('opening_hours')
            ->count();

        return response()->json([
            'total_locations'    => $totalLocations,
            'enabled_locations'  => $enabledLocations,
            'disabled_locations' => $disabledLocations,
            'default_locations'  => $defaultLocations,
            'by_type'            => $byType,
            'by_country'         => $byCountry,
            'with_coordinates'   => $withCoordinates,
            'with_opening_hours' => $withOpeningHours,
        ]);
    }

    /**
     * Normalise location collections so only records that are safe to render reach the caller.
     *
     * @param  Collection<int, Location> $locations
     * @return Collection<int, Location>
     */
    private function filterDisplayableLocations(Collection $locations): Collection
    {
        // Filtering on the collection keeps the query readable while still trimming any incomplete
        // rows that slipped past legacy imports or partially filled admin forms.
        return $locations
            ->filter($this->isDisplayableLocation(...))
            ->values();
    }

    /**
     * Decide whether a location contains enough metadata to be shown to customers.
     */
    private function isDisplayableLocation(Location $location): bool
    {
        // We require the key presentation fields to avoid exposing blank rows or placeholders.
        return $location->is_enabled
            && filled($location->name)
            && filled($location->type)
            && filled($location->city)
            && filled($location->country_code);
    }
}
