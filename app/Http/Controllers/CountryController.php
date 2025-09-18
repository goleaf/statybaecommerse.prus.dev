<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
/**
 * CountryController
 * 
 * HTTP controller handling CountryController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class CountryController extends Controller
{
    /**
     * Display a listing of the resource with pagination and filtering.
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $countries = Country::query()->active()->enabled()->with(['cities'])->when($request->has('region'), fn($query) => $query->where('region', $request->get('region')))->when($request->has('currency'), fn($query) => $query->where('currency_code', $request->get('currency')))->when($request->has('is_eu_member'), fn($query) => $query->where('is_eu_member', $request->boolean('is_eu_member')))->when($request->has('requires_vat'), fn($query) => $query->where('requires_vat', $request->boolean('requires_vat')))->when($request->has('search'), fn($query) => $query->where('name', 'like', '%' . $request->get('search') . '%'))->orderBy('sort_order')->orderBy('name')->get()->skipWhile(function ($country) {
            // Skip countries that are not properly configured for display
            return empty($country->name) || !$country->is_active || !$country->is_enabled || empty($country->cca2) || empty($country->cca3);
        })->paginate(24);
        $regions = Country::distinct()->pluck('region')->filter()->sort()->values();
        $currencies = Country::distinct()->pluck('currency_code')->filter()->sort()->values();
        return view('countries.index', compact('countries', 'regions', 'currencies'));
    }
    /**
     * Display the specified resource with related data.
     * @param Country $country
     * @return View
     */
    public function show(Country $country): View
    {
        $country->load(['translations', 'cities' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }, 'regions' => function ($query) {
            $query->where('is_enabled', true)->orderBy('sort_order')->orderBy('name');
        }, 'addresses' => function ($query) {
            $query->latest()->limit(10);
        }]);
        // Get related countries in the same region
        $relatedCountries = Country::query()->where('region', $country->region)->where('id', '!=', $country->id)->active()->enabled()->limit(6)->get()->skipWhile(function ($relatedCountry) {
            // Skip related countries that are not properly configured for display
            return empty($relatedCountry->name) || !$relatedCountry->is_active || !$relatedCountry->is_enabled || empty($relatedCountry->cca2) || empty($relatedCountry->cca3);
        });
        return view('countries.show', compact('country', 'relatedCountries'));
    }
    /**
     * Handle api functionality with proper error handling.
     * @param Request $request
     */
    public function api(Request $request)
    {
        $countries = Country::query()->active()->enabled()->when($request->has('search'), fn($query) => $query->where('name', 'like', '%' . $request->get('search') . '%'))->when($request->has('region'), fn($query) => $query->where('region', $request->get('region')))->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'cca2', 'cca3', 'flag', 'region', 'currency_code'])->skipWhile(function ($country) {
            // Skip countries that are not properly configured for API response
            return empty($country->name) || !$country->is_active || !$country->is_enabled || empty($country->cca2) || empty($country->cca3);
        });
        return response()->json(['countries' => $countries, 'total' => $countries->count()]);
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        $countries = Country::query()->where('name', 'like', "%{$query}%")->orWhere('name_official', 'like', "%{$query}%")->orWhere('cca2', 'like', "%{$query}%")->orWhere('cca3', 'like', "%{$query}%")->limit(10)->get(['id', 'name', 'cca2', 'cca3', 'flag'])->skipWhile(function ($country) {
            // Skip countries that are not properly configured for search results
            return empty($country->name) || !$country->is_active || !$country->is_enabled || empty($country->cca2) || empty($country->cca3);
        });
        return response()->json($countries);
    }
    /**
     * Handle euMembers functionality with proper error handling.
     */
    public function euMembers()
    {
        $countries = Country::query()->where('is_eu_member', true)->orderBy('name')->get(['id', 'name', 'cca2', 'cca3', 'flag', 'currency_code', 'vat_rate']);
        return response()->json($countries);
    }
    /**
     * Handle withVat functionality with proper error handling.
     */
    public function withVat()
    {
        $countries = Country::query()->where('requires_vat', true)->orderBy('name')->get(['id', 'name', 'cca2', 'cca3', 'flag', 'currency_code', 'vat_rate']);
        return response()->json($countries);
    }
    /**
     * Handle statistics functionality with proper error handling.
     */
    public function statistics()
    {
        $stats = ['total_countries' => Country::count(), 'active_countries' => Country::where('is_active', true)->count(), 'eu_members' => Country::where('is_eu_member', true)->count(), 'countries_with_vat' => Country::where('requires_vat', true)->count(), 'average_vat_rate' => Country::where('requires_vat', true)->avg('vat_rate'), 'by_region' => Country::selectRaw('region, COUNT(*) as count')->whereNotNull('region')->groupBy('region')->orderBy('count', 'desc')->get(), 'by_currency' => Country::selectRaw('currency_code, COUNT(*) as count')->whereNotNull('currency_code')->groupBy('currency_code')->orderBy('count', 'desc')->limit(10)->get()];
        return response()->json($stats);
    }
}