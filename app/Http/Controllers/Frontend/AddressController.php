<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Region;
use App\Models\Zone;
use App\Models\City;
use App\Enums\AddressType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

final class AddressController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        $addressTypes = AddressType::options();
        
        return view('addresses.create', compact('countries', 'addressTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', AddressType::values()),
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_vat' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'country_id' => 'nullable|exists:countries,id',
            'zone_id' => 'nullable|exists:zones,id',
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_default' => 'boolean',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:1000',
            'landmark' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();
        $data['is_active'] = true;

        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            Address::where('user_id', Auth::id())
                ->update(['is_default' => false]);
        }

        $address = Address::create($data);

        return redirect()->route('frontend.addresses.index')
            ->with('success', __('translations.address_created_successfully'));
    }

    public function show(Address $address): View
    {
        $this->authorize('view', $address);
        
        return view('addresses.show', compact('address'));
    }

    public function edit(Address $address): View
    {
        $this->authorize('update', $address);
        
        $countries = Country::orderBy('name')->get();
        $addressTypes = AddressType::options();
        
        return view('addresses.edit', compact('address', 'countries', 'addressTypes'));
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:' . implode(',', AddressType::values()),
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_vat' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'country_id' => 'nullable|exists:countries,id',
            'zone_id' => 'nullable|exists:zones,id',
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_default' => 'boolean',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:1000',
            'landmark' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            Address::where('user_id', Auth::id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('frontend.addresses.index')
            ->with('success', __('translations.address_updated_successfully'));
    }

    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);
        
        $address->delete();

        return redirect()->route('frontend.addresses.index')
            ->with('success', __('translations.address_deleted_successfully'));
    }

    public function setDefault(Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        
        $address->setAsDefault();

        return redirect()->route('frontend.addresses.index')
            ->with('success', __('translations.address_set_as_default'));
    }

    public function duplicate(Address $address): RedirectResponse
    {
        $this->authorize('view', $address);
        
        $newAddress = $address->duplicateForUser(Auth::id());

        return redirect()->route('frontend.addresses.edit', $newAddress)
            ->with('success', __('translations.address_duplicated'));
    }

    public function getCountries(): \Illuminate\Http\JsonResponse
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca2']);
        
        return response()->json($countries);
    }

    public function getRegions(Request $request): \Illuminate\Http\JsonResponse
    {
        $regions = Region::where('country_id', $request->country_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($regions);
    }

    public function getZones(Request $request): \Illuminate\Http\JsonResponse
    {
        $zones = Zone::where('country_id', $request->country_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($zones);
    }

    public function getCities(Request $request): \Illuminate\Http\JsonResponse
    {
        $cities = City::where('region_id', $request->region_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($cities);
    }
}
