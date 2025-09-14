<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\View\View;

final /**
 * CityController
 * 
 * HTTP controller handling web requests and responses.
 */
class CityController extends Controller
{
    public function index(Request $request): View
    {
        $query = City::with(['country', 'region', 'zone', 'parent'])
            ->withTranslations()
            ->enabled()
            ->active();

        // Search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('translations', function ($translationQuery) use ($search) {
                      $translationQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Country filter
        if ($request->filled('country')) {
            $query->where('country_id', $request->get('country'));
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('level', $request->get('level'));
        }

        // Capital cities filter
        if ($request->boolean('capital')) {
            $query->capital();
        }

        // Cities with population filter
        if ($request->boolean('with_population')) {
            $query->where('population', '>', 0);
        }

        // Cities with coordinates filter
        if ($request->boolean('with_coordinates')) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        // Sort by default
        $query->ordered()->orderBy('name');

        $cities = $query->get()
            ->skipWhile(function ($city) {
                // Skip cities that are not properly configured for display
                return empty($city->name) || 
                       !$city->is_enabled ||
                       !$city->is_active ||
                       empty($city->country_id) ||
                       empty($city->code);
            })
            ->paginate(24);

        // Get countries for filter dropdown
        $countries = Country::withTranslations()
            ->enabled()
            ->active()
            ->ordered()
            ->orderBy('name')
            ->get();

        return view('cities.index', compact('cities', 'countries'));
    }

    public function show(City $city): View
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$city->relationLoaded('country.translations') || !$city->relationLoaded('region.translations') || 
            !$city->relationLoaded('zone.translations') || !$city->relationLoaded('parent.translations') || 
            !$city->relationLoaded('children.translations') || !$city->relationLoaded('translations')) {
            $city->load([
                'country.translations',
                'region.translations',
                'zone.translations',
                'parent.translations',
                'children.translations',
                'translations'
            ]);
        }

        // Get nearby cities (within 50km radius if coordinates available)
        $nearbyCities = collect();
        if ($city->latitude && $city->longitude) {
            $nearbyCities = City::withTranslations()
                ->enabled()
                ->active()
                ->where('id', '!=', $city->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
                ->skipWhile(function ($nearbyCity) {
                    // Skip nearby cities that are not properly configured for display
                    return empty($nearbyCity->name) || 
                           !$nearbyCity->is_enabled ||
                           !$nearbyCity->is_active ||
                           empty($nearbyCity->country_id) ||
                           empty($nearbyCity->code);
                })
                ->filter(function ($nearbyCity) use ($city) {
                    $distance = $this->calculateDistance(
                        $city->latitude,
                        $city->longitude,
                        $nearbyCity->latitude,
                        $nearbyCity->longitude
                    );
                    return $distance <= 50; // Within 50km
                })
                ->sortBy(function ($nearbyCity) use ($city) {
                    return $this->calculateDistance(
                        $city->latitude,
                        $city->longitude,
                        $nearbyCity->latitude,
                        $nearbyCity->longitude
                    );
                })
                ->take(5);
        }

        // Get related cities (same country, region, or level)
        $relatedCities = City::withTranslations()
            ->enabled()
            ->active()
            ->where('id', '!=', $city->id)
            ->where(function ($q) use ($city) {
                $q->where('country_id', $city->country_id)
                  ->orWhere('region_id', $city->region_id)
                  ->orWhere('level', $city->level);
            })
            ->limit(6)
            ->get()
            ->skipWhile(function ($relatedCity) {
                // Skip related cities that are not properly configured for display
                return empty($relatedCity->name) || 
                       !$relatedCity->is_enabled ||
                       !$relatedCity->is_active ||
                       empty($relatedCity->country_id) ||
                       empty($relatedCity->code);
            });

        return view('cities.show', compact('city', 'nearbyCities', 'relatedCities'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (empty($query)) {
            return response()->json([]);
        }

        $cities = City::with(['country', 'region'])
            ->withTranslations()
            ->enabled()
            ->active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%")
                  ->orWhereHas('translations', function ($translationQuery) use ($query) {
                      $translationQuery->where('name', 'like', "%{$query}%");
                  });
            })
            ->limit($limit)
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->translated_name,
                    'code' => $city->code,
                    'country' => $city->country?->translated_name,
                    'region' => $city->region?->translated_name,
                    'url' => route('cities.show', $city),
                ];
            });

        return response()->json($cities);
    }

    public function byCountry(Country $country): View
    {
        $cities = City::with(['region', 'zone'])
            ->withTranslations()
            ->enabled()
            ->active()
            ->byCountry($country->id)
            ->ordered()
            ->orderBy('name')
            ->paginate(24);

        return view('cities.by-country', compact('cities', 'country'));
    }

    public function byRegion($regionId): View
    {
        $cities = City::with(['country', 'zone'])
            ->withTranslations()
            ->enabled()
            ->active()
            ->byRegion($regionId)
            ->ordered()
            ->orderBy('name')
            ->paginate(24);

        $region = $cities->first()?->region;

        return view('cities.by-region', compact('cities', 'region'));
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
