<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use App\Support\Address\AddressDataSanitizer;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Addresses
 *
 * Livewire component for Addresses with reactive frontend functionality, real-time updates, and user interaction handling.
 */
#[Layout('components.layouts.templates.account')]
class Addresses extends Component
{
    public ?string $first_name = null;

    public ?string $last_name = null;

    public ?string $address_line_1 = null;

    public ?string $address_line_2 = null;

    public ?string $city = null;

    public ?string $postal_code = null;

    public ?string $country_code = null;

    public ?string $phone = null;

    public string $type = 'shipping';

    public bool $set_as_default = false;

    public ?int $editing_address_id = null;

    /**
     * @var array<string, string>
     */
    public array $countries = [];

    public function mount(): void
    {
        $this->countries = $this->resolvedCountryOptions();
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    protected function rules(): array
    {
        $allowedCountries = $this->allowedCountries();
        $countryRules = ['required', 'string', 'size:2', Rule::in(array_keys($this->countries))];

        if ($this->countries === [] && $allowedCountries === []) {
            $countryRules[] = 'exists:countries,cca2';
        }

        return [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'min:3', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
            'country_code'   => $countryRules,
            'phone'          => ['nullable', 'string', 'max:30'],
            'type'           => ['required', Rule::in(['billing', 'shipping'])],
            'set_as_default' => ['boolean'],
        ];
    }

    public function saveAddress(): void
    {
        $this->validate();

        try {
            $user = Auth::user();

            if (! $user) {
                abort(401, __('messages.unauthorized'));
            }

            if ($this->editing_address_id !== null) {
                $this->updateAddress($user);

                return;
            }

            $addressData = AddressDataSanitizer::sanitize([
                'type'           => $this->type,
                'first_name'     => $this->first_name,
                'last_name'      => $this->last_name,
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city'           => $this->city,
                'postal_code'    => $this->postal_code,
                'country_code'   => $this->country_code,
                'phone'          => $this->phone,
                'is_active'      => true,
                'is_billing'     => $this->type === 'billing',
                'is_shipping'    => $this->type === 'shipping',
                'is_default'     => $this->set_as_default,
            ], $this->country_code);

            $hasAddressesOfSameType = $user->addresses()
                ->where('type', $this->type)
                ->where('is_active', true)
                ->exists();

            if (! $hasAddressesOfSameType) {
                $addressData['is_default'] = true;
            }

            if (($addressData['is_default'] ?? false) === true) {
                $user->addresses()
                    ->where('type', $this->type)
                    ->update(['is_default' => false]);
            }

            $user->addresses()->create($addressData);

            Notification::make()
                ->title(__('messages.address_added_successfully'))
                ->success()
                ->send();

            $this->resetForm();
            $this->dispatch('addresses-updated');
        } catch (Exception $e) {
            Notification::make()
                ->title(__('messages.error'))
                ->body(__('messages.failed_to_save_address_please_try_again'))
                ->danger()
                ->send();
        }
    }

    public function editAddress(int $id): void
    {
        $address = Address::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (! $address) {
            Notification::make()
                ->title(__('messages.unauthorized'))
                ->body(__('messages.you_are_not_authorized_to_modify_this_address'))
                ->danger()
                ->send();

            return;
        }

        $this->editing_address_id = $address->id;
        $this->first_name = $address->first_name;
        $this->last_name = $address->last_name;
        $this->address_line_1 = $address->address_line_1;
        $this->address_line_2 = $address->address_line_2;
        $this->city = $address->city;
        $this->postal_code = $address->postal_code;
        $this->country_code = $address->country_code !== null ? Str::upper($address->country_code) : null;
        $this->phone = $address->phone;
        $this->type = in_array($address->type, ['billing', 'shipping'], true) ? $address->type : 'shipping';
        $this->set_as_default = (bool) $address->is_default;

        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm(): void
    {
        $this->first_name = null;
        $this->last_name = null;
        $this->address_line_1 = null;
        $this->address_line_2 = null;
        $this->city = null;
        $this->postal_code = null;
        $this->country_code = null;
        $this->phone = null;
        $this->type = 'shipping';
        $this->set_as_default = false;
        $this->editing_address_id = null;
    }

    private function updateAddress(User $user): void
    {
        $address = $user->addresses()
            ->where('id', $this->editing_address_id)
            ->where('is_active', true)
            ->first();

        if (! $address) {
            Notification::make()
                ->title(__('messages.unauthorized'))
                ->body(__('messages.you_are_not_authorized_to_modify_this_address'))
                ->danger()
                ->send();

            return;
        }

        $addressData = AddressDataSanitizer::sanitize([
            'type'           => $this->type,
            'first_name'     => $this->first_name,
            'last_name'      => $this->last_name,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city'           => $this->city,
            'postal_code'    => $this->postal_code,
            'country_code'   => $this->country_code,
            'phone'          => $this->phone,
            'is_billing'     => $this->type === 'billing',
            'is_shipping'    => $this->type === 'shipping',
            'is_default'     => $this->set_as_default,
        ], $this->country_code);

        $hasAddressesOfSameType = $user->addresses()
            ->where('type', $this->type)
            ->where('id', '!=', $address->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasAddressesOfSameType) {
            $addressData['is_default'] = true;
        }

        if (($addressData['is_default'] ?? false) === true) {
            $user->addresses()
                ->where('type', $this->type)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($addressData);

        Notification::make()
            ->title(__('messages.address_updated_successfully'))
            ->success()
            ->send();

        $this->resetForm();
        $this->dispatch('addresses-updated');
    }

    /**
     * @return array<int, string>
     */
    private function allowedCountries(): array
    {
        $configured = config('addresses.allowed_countries', []);

        if ($configured === []) {
            return [];
        }

        return collect($configured)
            ->filter(static fn ($country): bool => is_string($country) && $country !== '')
            ->map(static fn (string $country): string => Str::upper($country))
            ->values()
            ->all();
    }

    /**
     * Resolve countries for the dropdown using locale translations first,
     * then fallback to active countries stored in the database.
     *
     * @return array<string, string>
     */
    private function resolvedCountryOptions(): array
    {
        $allowedCountries = $this->allowedCountries();

        $translatedCountries = __('addresses.countries');
        $countryOptions = collect(is_array($translatedCountries) ? $translatedCountries : [])
            ->filter(
                static fn ($name, $code): bool => is_string($name) && $name !== '' && is_string($code) && $code !== ''
            )
            ->mapWithKeys(static fn (string $name, string $code): array => [Str::upper($code) => $name]);

        if ($allowedCountries !== []) {
            $countryOptions = $countryOptions->only($allowedCountries);
        }

        if ($countryOptions->isEmpty()) {
            $countryOptions = Country::query()
                ->where('is_active', true)
                ->when($allowedCountries !== [], static fn ($query) => $query->whereIn('cca2', $allowedCountries))
                ->orderBy('name')
                ->pluck('name', 'cca2')
                ->mapWithKeys(static fn (string $name, string $code): array => [Str::upper($code) => $name]);
        }

        return $countryOptions
            ->sort()
            ->all();
    }

    /**
     * Handle removeAddress functionality with proper error handling.
     */
    public function removeAddress(int $id): void
    {
        try {
            $address = Address::query()->findOrFail($id);

            // Check if user owns this address
            if ($address->user_id !== Auth::id()) {
                Notification::make()
                    ->title(__('messages.unauthorized'))
                    ->body(__('messages.you_are_not_authorized_to_delete_this_address'))
                    ->danger()
                    ->send();

                return;
            }

            $address->delete();

            Notification::make()
                ->title(__('messages.address_deleted_successfully'))
                ->body(__('messages.the_address_has_been_removed_from_your_list'))
                ->success()
                ->send();

            $this->dispatch('addresses-updated');
        } catch (Exception $e) {
            Notification::make()
                ->title(__('messages.error'))
                ->body(__('messages.failed_to_delete_address_please_try_again'))
                ->danger()
                ->send();
        }
    }

    /**
     * Handle setDefaultAddress functionality with proper error handling.
     */
    public function setDefaultAddress(int $id): void
    {
        try {
            $address = Address::query()->findOrFail($id);

            // Check if user owns this address
            if ($address->user_id !== Auth::id()) {
                Notification::make()
                    ->title(__('messages.unauthorized'))
                    ->body(__('messages.you_are_not_authorized_to_modify_this_address'))
                    ->danger()
                    ->send();

                return;
            }

            // Remove default from other addresses of the same type
            Auth::user()->addresses()
                ->where('type', $address->type)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);

            // Set this address as default
            $address->update(['is_default' => true]);

            Notification::make()
                ->title(__('messages.default_address_updated'))
                ->body(__('messages.the_address_has_been_set_as_your_default_type_address', ['type' => $address->type]))
                ->success()
                ->send();

            $this->dispatch('addresses-updated');
        } catch (Exception $e) {
            Notification::make()
                ->title(__('messages.error'))
                ->body(__('messages.failed_to_set_default_address_please_try_again'))
                ->danger()
                ->send();
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    #[On('addresses-updated')]
    public function render(): View
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, __('messages.unauthorized'));
        }

        $addresses = $user->addresses()
            ->with('country')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.pages.account.addresses', [
            'addresses' => $addresses,
        ])->title(__('frontend.account.navigation.addresses'));
    }
}
