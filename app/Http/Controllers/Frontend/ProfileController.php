<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    private ?bool $customersTableExists = null;

    private ?bool $countriesTableExists = null;

    private ?bool $citiesTableExists = null;

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
        ];

        $data = $request->validate($rules);

        foreach (['is_default', 'is_billing', 'is_shipping'] as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        return $data;
    }

    private function synchroniseAddressFlags(User $user, Address $address): void
    {
        if ($address->is_default) {
            $user->addresses()
                ->whereKeyNot($address->getKey())
                ->update(['is_default' => false]);
        }

        if ($address->is_billing) {
            $user->addresses()
                ->whereKeyNot($address->getKey())
                ->update(['is_billing' => false]);
        }

        if ($address->is_shipping) {
            $user->addresses()
                ->whereKeyNot($address->getKey())
                ->update(['is_shipping' => false]);
        }
    }

    private function ensureAddressOwner(int $userId, Address $address): void
    {
        if ($address->user_id !== $userId) {
            abort(403, __('translations.unauthorized_action'));
        }
    }

    private function resolveCustomerForUser(User $user): ?Customer
    {
        if (! $this->customersTableExists()) {
            return null;
        }

        return Customer::query()
            ->where('email', $user->email)
            ->first();
    }

    private function updateCustomerRecord(User $user, array $validated, string $originalEmail): void
    {
        if (! $this->customersTableExists()) {
            return;
        }

        $customer = Customer::query()->where('email', $originalEmail)->first();

        if (! $customer) {
            $customer = Customer::query()->firstOrNew(['email' => $validated['email']]);
        }

        $customer->fill([
            'name' => $user->name,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
        ]);

        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);
        $validated['is_billing'] = (bool) ($validated['is_billing'] ?? false);
        $validated['is_shipping'] = (bool) ($validated['is_shipping'] ?? false);

        return $validated;
    }

    private function ensureSingleDefault(Request $request, Address $address): void
    {
        $rules = ['nullable', 'integer'];

        if ($this->countriesTableExists()) {
            $rules[] = Rule::exists('countries', 'id');
        }

        return $rules;
    }

    /**
     * @return array<int, string|Exists>
     */
    private function cityRule(): array
    {
        $rules = ['nullable', 'integer'];

        if ($this->citiesTableExists()) {
            $rules[] = Rule::exists('cities', 'id');
        }

        return $rules;
    }

    private function resolveCountries(): Collection
    {
        if (! $this->countriesTableExists()) {
            return collect();
        }

        return Country::query()->orderBy('name')->get(['id', 'name', 'cca2']);
    }

    private function customersTableExists(): bool
    {
        if ($this->customersTableExists === null) {
            $this->customersTableExists = Schema::hasTable('customers');
        }

        return $this->customersTableExists;
    }

    private function countriesTableExists(): bool
    {
        if ($this->countriesTableExists === null) {
            $this->countriesTableExists = Schema::hasTable('countries');
        }

        return $this->countriesTableExists;
    }

    private function citiesTableExists(): bool
    {
        if ($this->citiesTableExists === null) {
            $this->citiesTableExists = Schema::hasTable('cities');
        }

        return $this->citiesTableExists;
    }
}
