<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Country;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

final class ProfileController extends Controller
{
    private ?bool $countriesTableExists = null;

    private ?bool $citiesTableExists = null;

    private ?bool $customersTableExists = null;

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing([
            'addresses' => static fn ($query) => $query->orderByDesc('is_default')->orderByDesc('created_at'),
        ]);

        return view('profile.index', [
            'user' => $user,
            'customer' => $this->resolveCustomerForUser($user),
            'addresses' => $user->addresses,
        ]);
    }

    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing('addresses');

        return view('profile.edit', [
            'user' => $user,
            'customer' => $this->resolveCustomerForUser($user),
            'countries' => $this->resolveCountries(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $originalEmail = (string) $user->email;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country_id' => $this->countryRule(),
            'city_id' => $this->cityRule(),
        ]);

        $user->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'phone_number' => $validated['phone'] ?? null,
        ])->save();

        $this->updateCustomerRecord($user, $validated, $originalEmail);

        return redirect()
            ->route('frontend.profile.index')
            ->with('success', __('translations.profile_updated_successfully'));
    }

    public function addresses(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('profile.addresses', [
            'user' => $user,
            'addresses' => $addresses,
            'addressTypes' => AddressType::options(),
            'countries' => $this->resolveCountries(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $this->validateAddress($request);

        $address = new Address($validated);
        $address->user_id = $user->id;
        $address->is_active = true;
        $address->save();

        $this->synchroniseAddressFlags($user, $address);

        return redirect()
            ->route('frontend.profile.addresses')
            ->with('success', __('translations.address_created_successfully'));
    }

    public function updateAddress(Request $request, Address $address): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->ensureAddressOwner($user->id, $address);

        $validated = $this->validateAddress($request);
        $address->fill($validated);
        $address->save();

        $this->synchroniseAddressFlags($user, $address);

        return redirect()
            ->route('frontend.profile.addresses')
            ->with('success', __('translations.address_updated_successfully'));
    }

    public function deleteAddress(Request $request, Address $address): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->ensureAddressOwner($user->id, $address);

        $address->delete();

        return redirect()
            ->route('frontend.profile.addresses')
            ->with('success', __('translations.address_deleted_successfully'));
    }

    private function validateAddress(Request $request): array
    {
        $rules = [
            'type' => ['required', Rule::in(AddressType::values())],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_vat' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'apartment' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:100'],
            'building' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'size:2'],
            'country_id' => $this->countryRule(),
            'city_id' => $this->cityRule(),
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'landmark' => ['nullable', 'string', 'max:255'],
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

        if (! $customer->exists) {
            $customer->is_active = true;
        }

        $customer->save();
    }

    /**
     * @return array<int, string|Exists>
     */
    private function countryRule(): array
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

    private function customersTableExists(): bool
    {
        if ($this->customersTableExists === null) {
            $this->customersTableExists = Schema::hasTable('customers');
        }

        return $this->customersTableExists;
    }
}
