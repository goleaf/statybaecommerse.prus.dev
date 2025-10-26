<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Country\CountryApiRequest;
use App\Http\Requests\Country\CountryIndexRequest;
use App\Http\Requests\Country\CountrySearchRequest;
use App\Http\Resources\CountryResource;
use App\Http\Resources\CountryStatisticsResource;
use App\Models\Country;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * CountryController
 *
 * HTTP controller handling CountryController related web requests, responses, and business logic with proper validation and error handling.
 */
final class CountryController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(CountryIndexRequest $request): View
    {
        $this->authorize('viewAny', Country::class);

        // Retrieve validated filter, sort and pagination data from the request object.
        $filters = $request->validated();

        // Apply eager loading and allow-listed filters to the country listing query.
        $countriesQuery = Country::query()
            ->active()
            ->enabled()
            ->with(['cities'])
            ->when(isset($filters['region']), static function ($query) use ($filters): void {
                $query->where('region', $filters['region']);
            })
            ->when(isset($filters['currency']), static function ($query) use ($filters): void {
                $query->where('currency_code', $filters['currency']);
            })
            ->when(array_key_exists('is_eu_member', $filters), static function ($query) use ($filters): void {
                $query->where('is_eu_member', (bool) $filters['is_eu_member']);
            })
            ->when(array_key_exists('requires_vat', $filters), static function ($query) use ($filters): void {
                $query->where('requires_vat', (bool) $filters['requires_vat']);
            })
            ->when(isset($filters['search']), static function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->whereNotNull('name')
            ->whereNotNull('cca2')
            ->whereNotNull('cca3');

        // Resolve the requested sort order while constraining to known fields.
        $sort = $filters['sort'] ?? 'name';
        $direction = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';
        $column = ltrim((string) $sort, '-');
        $countriesQuery->orderBy($column === 'sort_order' ? 'sort_order' : 'name', $direction);

        // Paginate the results using the requested page size and preserve the query string.
        $perPage = $filters['per_page'] ?? 24;
        $countries = $countriesQuery->paginate($perPage)->withQueryString();

        // Fetch filter metadata collections to drive the frontend dropdown options.
        $regions = Country::query()->distinct()->pluck('region')->filter()->sort()->values();
        $currencies = Country::query()->distinct()->pluck('currency_code')->filter()->sort()->values();

        return view('countries.index', compact('countries', 'regions', 'currencies'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Country $country): View
    {
        $this->authorize('view', $country);

        // Eager load related entities in bulk to avoid N+1 problems in the blade views.
        $country->load([
            'translations',
            'cities' => static function ($query): void {
                $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            },
            'regions' => static function ($query): void {
                $query->where('is_enabled', true)->orderBy('sort_order')->orderBy('name');
            },
            'addresses' => static function ($query): void {
                $query->latest()->limit(10);
            },
        ]);

        // Fetch a curated list of related countries in the same region for recommendation blocks.
        $relatedCountries = Country::query()
            ->where('region', $country->region)
            ->where('id', '!=', $country->id)
            ->active()
            ->enabled()
            ->limit(6)
            ->get()
            ->filter(static function (Country $relatedCountry): bool {
                return ! empty($relatedCountry->name)
                    && ! empty($relatedCountry->cca2)
                    && ! empty($relatedCountry->cca3)
                    && (bool) $relatedCountry->is_active
                    && (bool) $relatedCountry->is_enabled;
            });

        return view('countries.show', compact('country', 'relatedCountries'));
    }

    /**
     * Handle api functionality with proper error handling.
     */
    public function api(CountryApiRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Country::class);

        // Gather the validated search criteria for building the API query.
        $filters = $request->validated();

        // Build the paginated query using explicit eager loading and filter handling.
        $countriesQuery = Country::query()
            ->active()
            ->enabled()
            ->with(['translations'])
            ->when(isset($filters['search']), static function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when(isset($filters['region']), static function ($query) use ($filters): void {
                $query->where('region', $filters['region']);
            })
            ->whereNotNull('name')
            ->whereNotNull('cca2')
            ->whereNotNull('cca3');

        $sort = $filters['sort'] ?? 'name';
        $direction = str_starts_with((string) $sort, '-') ? 'desc' : 'asc';
        $column = ltrim((string) $sort, '-');
        $countriesQuery->orderBy($column === 'sort_order' ? 'sort_order' : 'name', $direction);

        $perPage = $filters['per_page'] ?? 25;
        $countries = $countriesQuery->paginate($perPage)->withQueryString();

        return CountryResource::collection($countries);
    }

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(CountrySearchRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Country::class);

        // Extract the validated query string and pagination size for search responses.
        $filters = $request->validated();

        $countries = Country::query()
            ->active()
            ->enabled()
            ->where(static function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('name_official', 'like', '%'.$filters['q'].'%')
                    ->orWhere('cca2', 'like', '%'.$filters['q'].'%')
                    ->orWhere('cca3', 'like', '%'.$filters['q'].'%');
            })
            ->whereNotNull('name')
            ->whereNotNull('cca2')
            ->whereNotNull('cca3')
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        return CountryResource::collection($countries);
    }

    /**
     * Handle euMembers functionality with proper error handling.
     */
    public function euMembers(): AnonymousResourceCollection
    {
        $this->authorize('viewEuMembers', Country::class);

        // Return a resource collection of countries flagged as EU members.
        $countries = Country::query()
            ->active()
            ->enabled()
            ->where('is_eu_member', true)
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return CountryResource::collection($countries);
    }

    /**
     * Handle withVat functionality with proper error handling.
     */
    public function withVat(): AnonymousResourceCollection
    {
        $this->authorize('viewVatCountries', Country::class);

        // Return a resource collection highlighting countries that require VAT.
        $countries = Country::query()
            ->active()
            ->enabled()
            ->where('requires_vat', true)
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return CountryResource::collection($countries);
    }

    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics(): CountryStatisticsResource
    {
        $this->authorize('viewStatistics', Country::class);

        // Aggregate useful statistics for analytics dashboards.
        $stats = [
            'total_countries' => Country::count(),
            'active_countries' => Country::where('is_active', true)->count(),
            'eu_members' => Country::where('is_eu_member', true)->count(),
            'countries_with_vat' => Country::where('requires_vat', true)->count(),
            'average_vat_rate' => Country::where('requires_vat', true)->avg('vat_rate'),
            'by_region' => Country::selectRaw('region, COUNT(*) as count')
                ->whereNotNull('region')
                ->groupBy('region')
                ->orderBy('count', 'desc')
                ->get()
                ->toArray(),
            'by_currency' => Country::selectRaw('currency_code, COUNT(*) as count')
                ->whereNotNull('currency_code')
                ->groupBy('currency_code')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
        ];

        return new CountryStatisticsResource($stats);
    }
}
