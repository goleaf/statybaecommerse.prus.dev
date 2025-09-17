<?php declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Zone;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
     * @return View
     */
    public function index(): View
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->where('is_active', true)->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     * @return View
     */
    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        $addressTypes = AddressType::options();
        return view('addresses.create', compact('countries', 'addressTypes'));
    }

    /**
     * Store a newly created resource in storage with validation.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), ['type' => 'required|in:' . implode(',', AddressType::values()), 'first_name' => 'required|string|max:255', 'last_name' => 'required|string|max:255', 'company_name' => 'nullable|string|max:255', 'company_vat' => 'nullable|string|max:50', 'address_line_1' => 'required|string|max:255', 'address_line_2' => 'nullable|string|max:255', 'apartment' => 'nullable|string|max:100', 'floor' => 'nullable|string|max:100', 'building' => 'nullable|string|max:100', 'city' => 'required|string|max:100', 'state' => 'nullable|string|max:100', 'postal_code' => 'required|string|max:20', 'country_code' => 'required|string|size:2', 'country_id' => 'nullable|exists:countries,id', 'city_id' => 'nullable|exists:cities,id', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email|max:255', 'is_default' => 'boolean', 'is_billing' => 'boolean', 'is_shipping' => 'boolean', 'notes' => 'nullable|string|max:1000', 'instructions' => 'nullable|string|max:1000', 'landmark' => 'nullable|string|max:255']);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $data['user_id'] = Auth::id();
        $data['is_active'] = true;
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }
        $address = Address::create($data);
        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_created_successfully'));
    }

    /**
     * Display the specified resource with related data.
     * @param Address $address
     * @return View
     */
    public function show(Address $address): View
    {
        $this->authorize('view', $address);
        return view('addresses.show', compact('address'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param Address $address
     * @return View
     */
    public function edit(Address $address): View
    {
        $this->authorize('update', $address);
        $countries = Country::orderBy('name')->get();
        $addressTypes = AddressType::options();
        return view('addresses.edit', compact('address', 'countries', 'addressTypes'));
    }

    /**
     * Update the specified resource in storage with validation.
     * @param Request $request
     * @param Address $address
     * @return RedirectResponse
     */
    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        $validator = Validator::make($request->all(), ['type' => 'required|in:' . implode(',', AddressType::values()), 'first_name' => 'required|string|max:255', 'last_name' => 'required|string|max:255', 'company_name' => 'nullable|string|max:255', 'company_vat' => 'nullable|string|max:50', 'address_line_1' => 'required|string|max:255', 'address_line_2' => 'nullable|string|max:255', 'apartment' => 'nullable|string|max:100', 'floor' => 'nullable|string|max:100', 'building' => 'nullable|string|max:100', 'city' => 'required|string|max:100', 'state' => 'nullable|string|max:100', 'postal_code' => 'required|string|max:20', 'country_code' => 'required|string|size:2', 'country_id' => 'nullable|exists:countries,id', 'city_id' => 'nullable|exists:cities,id', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email|max:255', 'is_default' => 'boolean', 'is_billing' => 'boolean', 'is_shipping' => 'boolean', 'notes' => 'nullable|string|max:1000', 'instructions' => 'nullable|string|max:1000', 'landmark' => 'nullable|string|max:255']);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        // Ensure only one default address per user
        if ($data['is_default'] ?? false) {
            Address::where('user_id', Auth::id())->where('id', '!=', $address->id)->update(['is_default' => false]);
        }
        $address->update($data);
        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     * @param Address $address
     * @return RedirectResponse
     */
    public function destroy(Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);
        $address->delete();
        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_deleted_successfully'));
    }

    /**
     * Handle setDefault functionality with proper error handling.
     * @param Address $address
     * @return RedirectResponse
     */
    public function setDefault(Address $address): RedirectResponse
    {
        $this->authorize('update', $address);
        $address->setAsDefault();
        return redirect()->route('frontend.addresses.index')->with('success', __('translations.address_set_as_default'));
    }

    /**
     * Handle duplicate functionality with proper error handling.
     * @param Address $address
     * @return RedirectResponse
     */
    public function duplicate(Address $address): RedirectResponse
    {
        $this->authorize('view', $address);
        $newAddress = $address->duplicateForUser(Auth::id());
        return redirect()->route('frontend.addresses.edit', $newAddress)->with('success', __('translations.address_duplicated'));
    }

    /**
     * Handle getCountries functionality with proper error handling.
     * @return Illuminate\Http\JsonResponse
     */
    public function getCountries(): \Illuminate\Http\JsonResponse
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'cca2']);
        return response()->json($countries);
    }

    /**
     * Handle getZones functionality with proper error handling.
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getZones(Request $request): \Illuminate\Http\JsonResponse
    {
        $zones = Zone::where('country_id', $request->country_id)->orderBy('name')->get(['id', 'name']);
        return response()->json($zones);
    }

    /**
     * Handle getCities functionality with proper error handling.
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request): \Illuminate\Http\JsonResponse
    {
        $cities = City::where('country_id', $request->country_id)->orderBy('name')->get(['id', 'name']);
        return response()->json($cities);
    }
}
