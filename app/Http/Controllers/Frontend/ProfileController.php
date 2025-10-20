<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user?->orders()->latest()->limit(5)->get() ?? collect();
        $addresses = $user?->addresses()->latest()->limit(3)->get() ?? collect();

        $stats = [
            'orders_count' => $user?->orders()->count() ?? 0,
            'total_spent' => $user?->orders()->sum('total') ?? 0,
        ];

        return view('frontend.profile.index', [
            'user' => $user,
            'orders' => $orders,
            'addresses' => $addresses,
            'stats' => $stats,
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

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user->update($data);

        return redirect()->route('frontend.profile.index')->with('status', __('Profile updated successfully.'));
    }

    public function addresses(Request $request): View
    {
        return view('frontend.profile.addresses', [
            'user' => $request->user(),
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $addressData = $this->validateAddress($request);

        $user = $request->user();

        if ($addressData['is_default']) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($addressData);

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address added successfully.'));
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($request->user()->is($address->user), 403);

        $addressData = $this->validateAddress($request);

        if ($addressData['is_default']) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($addressData);

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address updated successfully.'));
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($request->user()->is($address->user), 403);

        $address->delete();

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address removed.'));
    }

    private function validateAddress(Request $request): array
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'country_code' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'is_default' => ['sometimes', 'boolean'],
            'is_billing' => ['sometimes', 'boolean'],
            'is_shipping' => ['sometimes', 'boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_billing'] = $request->boolean('is_billing');
        $validated['is_shipping'] = $request->boolean('is_shipping');
        $validated['type'] = $validated['type'] ?? 'shipping';
        $validated['user_id'] = $request->user()->id;

        return $validated;
    }
}
