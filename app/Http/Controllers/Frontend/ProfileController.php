<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use App\Support\Database\TableAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

final class ProfileController extends Controller
{
    public function __construct(private readonly TableAvailability $tables)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->resolveUser($request)->load('addresses');

        return response()->view('profile.index', [
            'user' => $user,
            'addresses' => $user->addresses,
            'customer' => $this->resolveCustomerForUser($user),
        ]);
    }

    public function edit(Request $request): Response
    {
        $user = $this->resolveUser($request);

        return response()->view('profile.edit', [
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

        $fullName = $validated['name'] ?? trim(implode(' ', array_filter([
            $validated['first_name'] ?? null,
            $validated['last_name'] ?? null,
        ], static fn ($value): bool => $value !== null && $value !== '')));

        if ($fullName === '') {
            $fullName = trim((string) $user->name);
        }

        if ($fullName === '') {
            $fullName = trim(implode(' ', array_filter([
                $user->first_name,
                $user->last_name,
            ], static fn ($value): bool => $value !== null && $value !== '')));
        }

        if ($fullName === '') {
            $fullName = (string) $user->email;
        }

        [$derivedFirstName, $derivedLastName] = $this->splitName($fullName);

        $user->name = $fullName;

        $user->first_name = $validated['first_name'] ?? $derivedFirstName ?? $user->first_name;
        $user->last_name = $validated['last_name'] ?? $derivedLastName ?? $user->last_name;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->phone_number = $validated['phone'] ?? null;

        if (! empty($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->updateCustomerRecord($user, $validated, $originalEmail);

        return redirect()->route('frontend.profile.index')->with('status', 'profile-updated');
    }

    public function addresses(Request $request): Response
    {
        $user = $this->resolveUser($request);

        return response()->view('profile.addresses', [
            'user' => $user,
            'addresses' => $user->addresses()->latest()->get(),
            'types' => AddressType::cases(),
            'addressTypes' => AddressType::options(),
            'countries' => $this->resolveCountries(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $user = $this->resolveUser($request);
        $validated = $this->validateAddress($request);

        $address = $user->addresses()->create($validated);

        if ($address->is_default) {
            $this->ensureSingleDefault($user, $address);
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
            $this->ensureSingleDefault($user, $address);
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
            'type' => ['required', Rule::in(array_map(static fn (AddressType $type) => $type->value, AddressType::cases()))],
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

    private function ensureSingleDefault(User $user, Address $address): void
    {
        $this->synchroniseAddressFlags($user, $address);
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

        $columns = Schema::hasTable($customer->getTable())
            ? Schema::getColumnListing($customer->getTable())
            : [];

        $columnLookup = array_flip($columns);

        $customer->fill(array_intersect_key([
            'name' => $user->name,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country_id' => $validated['country_id'] ?? null,
            'city_id' => $validated['city_id'] ?? null,
        ], $columnLookup));

        foreach (['is_default', 'is_billing', 'is_shipping'] as $flag) {
            if (isset($columnLookup[$flag])) {
                $customer->{$flag} = (bool) ($validated[$flag] ?? $customer->{$flag} ?? false);
            }
        }

        $customer->save();
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
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:120'],
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
        return $this->tables->has('customers');
    }

    private function countriesTableExists(): bool
    {
        return $this->tables->has('countries');
    }

    private function citiesTableExists(): bool
    {
        return $this->tables->has('cities');
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/u', trim($name), 2, PREG_SPLIT_NO_EMPTY) ?: [];

        $firstName = $parts[0] ?? $name;
        $lastName = $parts[1] ?? null;

        return [$firstName, $lastName];
    }
}
