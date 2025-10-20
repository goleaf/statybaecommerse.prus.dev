<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ProfileController extends Controller
{
    public function index(): View
    {
        return view('frontend.profile.index', [
            'user' => Auth::user(),
        ]);
    }

    public function edit(): View
    {
        return view('frontend.profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user->fill($data);
        $user->save();

        return redirect()->route('frontend.profile.index')->with('status', __('Profile updated successfully.'));
    }

    public function addresses(): View
    {
        $addresses = Address::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('frontend.profile.addresses', [
            'addresses' => $addresses,
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $this->validateAddress($request);
        $data['user_id'] = Auth::id();

        $address = Address::create($data);

        if ($address->is_default) {
            $this->clearOtherDefaultAddresses($address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address saved successfully.'));
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        if ($request->boolean('set_default')) {
            $address->update(['is_default' => true]);
            $this->clearOtherDefaultAddresses($address);

            return redirect()->route('frontend.profile.addresses')->with('status', __('Default address updated.'));
        }

        $data = $this->validateAddress($request);
        $address->update($data);

        if ($address->is_default) {
            $this->clearOtherDefaultAddresses($address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address updated successfully.'));
    }

    public function deleteAddress(Address $address): RedirectResponse
    {
        $this->authorizeAddress($address);
        $address->delete();

        return redirect()->route('frontend.profile.addresses')->with('status', __('Address removed.'));
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'country_code' => ['required', 'string', 'size:2'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
            'type' => ['nullable', 'string'],
        ]);
    }

    private function authorizeAddress(Address $address): void
    {
        abort_if($address->user_id !== Auth::id(), 403);
    }

    private function clearOtherDefaultAddresses(Address $address): void
    {
        Address::query()
            ->where('user_id', $address->user_id)
            ->whereKeyNot($address->getKey())
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
