<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('frontend.profile.index', [
            'user' => $request->user()->load('addresses'),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('frontend.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('frontend.profile.index')->with('status', 'profile-updated');
    }

    public function addresses(Request $request): View
    {
        return view('frontend.profile.addresses', [
            'addresses' => $request->user()->addresses()->latest()->get(),
            'types' => AddressType::cases(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);

        $address = $request->user()->addresses()->create($validated);

        if ($address->is_default) {
            $this->ensureSingleDefault($request, $address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-created');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $validated = $this->validateAddress($request, $address);

        $address->update($validated);

        if ($address->is_default) {
            $this->ensureSingleDefault($request, $address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-updated');
    }

    public function deleteAddress(Address $address): RedirectResponse
    {
        $address->delete();

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-deleted');
    }

    private function validateAddress(Request $request, ?Address $address = null): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_map(fn (AddressType $type) => $type->value, AddressType::cases()))],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:32'],
            'country_code' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'is_billing' => ['sometimes', 'boolean'],
            'is_shipping' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);
        $validated['is_billing'] = (bool) ($validated['is_billing'] ?? false);
        $validated['is_shipping'] = (bool) ($validated['is_shipping'] ?? false);

        return $validated;
    }

    private function ensureSingleDefault(Request $request, Address $address): void
    {
        $request->user()->addresses()
            ->whereKeyNot($address->getKey())
            ->update(['is_default' => false]);
    }
}
