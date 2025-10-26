<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\GetCitiesRequest;
use App\Http\Requests\Frontend\StoreAddressRequest;
use App\Http\Requests\Frontend\UpdateAddressRequest;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AddressController
 *
 * HTTP controller handling AddressController related web requests, responses, and business logic with proper validation and error handling.
 */
final class AddressController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource with pagination and filtering.
     */
    public function index(): View
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->where('is_active', true)->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $countries = $this->allowedCountriesQuery()->orderBy('name')->get();
        $addressTypes = AddressType::options();

        return view('addresses.create', compact('countries', 'addressTypes'));
    }

    /**
     * Store a newly created resource in storage with validation.
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $address = new Address($data);
        $address->user()->associate(Auth::user());
        $address->is_active = $address->is_active ?? true;

        // Ensure only one default address per user
        if ($address->is_default) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address->save();

        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_created_successfully'));
    }

    /**
     * Display the specified resource with related data.
     */
    public function show(Address $address): View
    {
        $this->authorize('view', $address);

        return view('addresses.show', compact('address'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address): View
    {
        $this->authorize('update', $address);
        $countries = $this->allowedCountriesQuery()->orderBy('name')->get();
        $addressTypes = AddressType::options();

        return view('addresses.edit', compact('address', 'countries', 'addressTypes'));
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        $data = $request->validated();

        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            Address::where('user_id', Auth::id())->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->fill($data);
        $address->save();

        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);
        $address->delete();

        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_deleted_successfully'));
    }

    /**
     * Handle setDefault functionality with proper error handling.
     */
    public function setDefault(Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        $address->setAsDefault();

        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_set_as_default'));
    }

    /**
     * Handle duplicate functionality with proper error handling.
     */
    public function duplicate(Address $address): RedirectResponse
    {
        $this->authorize('view', $address);
        $newAddress = $address->duplicateForUser(Auth::id());

        return redirect()->route('frontend.addresses.edit', $newAddress)->with('success', __('translations.address_duplicated'));
    }

    /**
     * Handle getCountries functionality with proper error handling.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getCountries(): JsonResponse
    {
        $countries = $this->allowedCountriesQuery()->orderBy('name')->get(['id', 'name', 'cca2']);

        return response()->json($countries);
    }

    /**
     * Handle getCities functionality with proper error handling.
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function getCities(GetCitiesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cities = City::where('country_id', $validated['country_id'])->orderBy('name')->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Build a query for countries restricted to the configured allow-list.
     */
    private function allowedCountriesQuery(): Builder
    {
        $allowed = config('addresses.allowed_countries', []);

        return Country::query()
            ->where('is_active', true)
            ->when($allowed !== [], fn ($query) => $query->whereIn('cca2', $allowed));
    }
}
