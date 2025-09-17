<?php

declare (strict_types=1);
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
        $query = Country::query()->where('is_active', true)->where('is_enabled', true);
        // Apply filters
        if ($request->filled('region')) {
            $query->where('region', $request->get('region'));
        }
        if ($request->filled('currency')) {
            $query->where('currency_code', $request->get('currency'));
        }
        if ($request->filled('eu_member')) {
            $query->where('is_eu_member', $request->boolean('eu_member'));
        }
        if ($request->filled('requires_vat')) {
            $query->where('requires_vat', $request->boolean('requires_vat'));
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('cca2', 'like', "%{$search}%")->orWhere('cca3', 'like', "%{$search}%")->orWhereHas('translations', function ($translationQuery) use ($search) {
                    $translationQuery->where('name', 'like', "%{$search}%")->orWhere('name_official', 'like', "%{$search}%");
                });
            });
        }
        // Apply sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        if (in_array($sortBy, ['name', 'region', 'currency_code', 'vat_rate'])) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
        }
        $countries = $query->paginate(24);
        // Get filter options
        $regions = Country::where('is_active', true)->whereNotNull('region')->distinct()->pluck('region')->sort()->values();
        $currencies = Country::where('is_active', true)->whereNotNull('currency_code')->distinct()->pluck('currency_code')->sort()->values();
        return view('countries.index', compact('countries', 'regions', 'currencies'));
    }
    /**
     * Display the specified resource with related data.
     * @param Country $country
     * @return View
     */
    public function show(Country $country): View
    {
        // Ensure country is active and enabled
        if (!$country->is_active || !$country->is_enabled) {
            abort(404);
        }
        // Load related data
        $country->load(['addresses' => function ($query) {
            $query->limit(10);
        }, 'cities' => function ($query) {
            $query->where('is_active', true)->limit(20);
        }, 'regions' => function ($query) {
            $query->where('is_active', true)->limit(10);
        }]);
        return view('countries.show', compact('country'));
    }
    /**
     * Handle getCountriesJson functionality with proper error handling.
     * @param Request $request
     */
    public function getCountriesJson(Request $request)
    {
        $query = Country::query()->where('is_active', true)->where('is_enabled', true)->select(['id', 'name', 'cca2', 'cca3', 'currency_code', 'phone_calling_code', 'flag']);
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('cca2', 'like', "%{$search}%")->orWhere('cca3', 'like', "%{$search}%");
            });
        }
        $countries = $query->orderBy('name')->limit(50)->get();
        return response()->json(['countries' => $countries->map(function ($country) {
            return ['id' => $country->id, 'name' => $country->translated_name, 'code' => $country->cca2, 'iso_code' => $country->cca3, 'currency' => $country->currency_code, 'phone_code' => $country->phone_calling_code, 'flag' => $country->getFlagUrl()];
        })]);
    }
}