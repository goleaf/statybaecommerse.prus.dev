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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

final class ProfileController extends Controller
{
    private ?bool $customersTableExists = null;

    private ?bool $countriesTableExists = null;

    private ?bool $citiesTableExists = null;

    public function index(Request $request): View
    {
        $user = $this->resolveUser($request);

        return view('profile.index', [
            'user' => $user,
            'addresses' => $user->addresses()->latest()->get(),
        ]);
    }

    public function edit(Request $request): View
    {
        $user = $this->resolveUser($request);

        return view('profile.edit', [
            'user' => $user,
            'countries' => $this->resolveCountries(),
            'customer' => $this->resolveCustomerForUser($user),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);
        $originalEmail = $user->email;

        $validated = $request->validate($this->profileRules($user));

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = trim(sprintf('%s %s', $validated['first_name'], $validated['last_name']));
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->phone_number = $validated['phone'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->updateCustomerRecord($user, $validated, $originalEmail);

        return redirect()->route('frontend.profile.index')->with('status', 'profile-updated');
    }

    public function addresses(Request $request): View
    {
        $user = $this->resolveUser($request);

        return view('profile.addresses', [
            'user' => $user,
            'addresses' => $user->addresses()->latest()->get(),
            'types' => AddressType::cases(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);
        $validated = $this->validateAddress($request);

        $address = $user->addresses()->create($validated);

        if ($address->is_default) {
            $this->ensureSingleDefault($request, $address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-created');
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        $user = $this->resolveUser($request);
        $this->ensureAddressOwner($user->getKey(), $address);

        $validated = $this->validateAddress($request, $address);

        $address->update($validated);

        if ($address->is_default) {
            $this->ensureSingleDefault($request, $address);
        }

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-updated');
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        $user = $this->resolveUser($request);
        $this->ensureAddressOwner($user->getKey(), $address);

        $address->delete();

        return redirect()->route('frontend.profile.addresses')->with('status', 'address-deleted');
    }

    private function validateAddress(Request $request, ?Address $address = null): array
    {
        $rules = [
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

        $customer->is_default = (bool) ($validated['is_default'] ?? $customer->is_default ?? false);
        $customer->is_billing = (bool) ($validated['is_billing'] ?? $customer->is_billing ?? false);
        $customer->is_shipping = (bool) ($validated['is_shipping'] ?? $customer->is_shipping ?? false);

        $customer->save();
    }

    private function ensureSingleDefault(Request $request, Address $address): void
    {
        $user = $this->resolveUser($request);

        $this->synchroniseAddressFlags($user, $address);
    }

    /**
     * @return array<int, string|Rule>
     */
    private function cityRule(): array
    {
        $rules = ['nullable', 'integer'];

        if ($this->citiesTableExists()) {
            $rules[] = Rule::exists('cities', 'id');
        }

        return $rules;
    }

    /**
     * @return array<int, string|Rule>
     */
    private function countryRule(): array
    {
        $rules = ['nullable', 'integer'];

        if ($this->countriesTableExists()) {
            $rules[] = Rule::exists('countries', 'id');
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

    /**
     * @return array<string, mixed>
     */
    private function profileRules(User $user): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:32'],
            'country_id' => $this->countryRule(),
            'city_id' => $this->cityRule(),
            'password' => ['nullable', 'confirmed', 'min:8'],
        ];
    }

    private function resolveUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
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
