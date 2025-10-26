<?php

declare(strict_types=1);

namespace App\Livewire\Modals\Account;

use App\Models\Address;
use App\Models\Country;
use App\Support\Address\AddressDataSanitizer;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use LivewireUI\Modal\ModalComponent;

/**
 * AddressForm
 *
 * Livewire component for AddressForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $street_address
 * @property string|null $street_address_plus
 * @property string $type
 * @property int|null $country_id
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $phone_number
 * @property Address|null $address
 * @property Collection $countries
 */
class AddressForm extends ModalComponent
{
    #[Validate('required|string|max:255')]
    public ?string $first_name = null;

    #[Validate('required|string|max:255')]
    public ?string $last_name = null;

    #[Validate('required|string|min:3|max:255')]
    public ?string $street_address = null;

    #[Validate('nullable|string|max:255')]
    public ?string $street_address_plus = null;

    #[Validate('required|in:billing,shipping')]
    public string $type = 'billing';

    #[Validate('required|string|size:2')]
    public ?string $country_code = null;

    #[Validate('required|string|max:20')]
    public ?string $postal_code = null;

    #[Validate('required|string|max:100')]
    public ?string $city = null;

    #[Validate('nullable|string|max:20')]
    public ?string $phone_number = null;

    public ?Address $address = null;

    public Collection $countries;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(?int $addressId = null): void
    {
        // Load countries using the configured allow-list to prevent unintended
        // leakage of address data into unsupported regions.
        $allowedCountries = $this->allowedCountries();

        $this->countries = Country::where('is_active', true)
            ->when($allowedCountries !== [], fn ($query) => $query->whereIn('cca2', $allowedCountries))
            ->orderBy('name')
            ->pluck('name', 'cca2');

        if ($addressId) {
            $this->address = Address::where('user_id', Auth::id())
                ->findOrFail($addressId);

            // Fill form with existing data
            $this->first_name = $this->address->first_name;
            $this->last_name = $this->address->last_name;
            $this->street_address = $this->address->address_line_1;
            $this->street_address_plus = $this->address->address_line_2;
            $this->city = $this->address->city;
            $this->postal_code = $this->address->postal_code;
            $this->country_code = $this->address->country_code;
            $this->phone_number = $this->address->phone;
            $this->type = $this->address->type->value;
        } else {
            $this->address = new Address;
        }
    }

    /**
     * Handle modalMaxWidth functionality with proper error handling.
     */
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();

        try {
            $addressData = AddressDataSanitizer::sanitize([
                'type' => $this->type,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'address_line_1' => $this->street_address,
                'address_line_2' => $this->street_address_plus,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country_code' => $this->country_code,
                'phone' => $this->phone_number,
                'is_active' => true,
                'is_billing' => $this->type === 'billing',
                'is_shipping' => $this->type === 'shipping',
            ], $this->country_code);

            $this->assertAllowListCompliance($addressData);

            // Check if this is the first address of this type, make it default
            $existingAddresses = Auth::user()->addresses()
                ->where('type', $this->type)
                ->where('is_active', true)
                ->count();

            if ($existingAddresses === 0 || ! $this->address->id) {
                $addressData['is_default'] = true;

                // Remove default from other addresses of the same type
                Auth::user()->addresses()
                    ->where('type', $this->type)
                    ->where('id', '!=', $this->address->id ?? 0)
                    ->update(['is_default' => false]);
            } else {
                $addressData['is_default'] = $this->address->is_default ?? false;
            }

            if ($this->address->id) {
                // Update existing address
                $this->address->update($addressData);
                $message = __('Address updated successfully');
            } else {
                // Create new address
                $address = new Address($addressData);
                $address->user()->associate(Auth::user());
                $address->save();
                $message = __('Address added successfully');
            }

            Notification::make()
                ->title($message)
                ->success()
                ->send();

            $this->dispatch('addresses-updated');
            $this->closeModal();

        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0] ?? __('Failed to save address.'));
            }
            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to save address. Please review the highlighted fields.'))
                ->danger()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to save address. Please try again.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Ensure the provided address fragments comply with configured allow-lists.
     *
     * @param array<string, mixed> $addressData
     *
     * @throws ValidationException
     */
    private function assertAllowListCompliance(array $addressData): void
    {
        $allowedCountries = $this->allowedCountries();
        $country = Str::upper((string) ($addressData['country_code'] ?? ''));

        if ($country === '' || ($allowedCountries !== [] && ! in_array($country, $allowedCountries, true))) {
            throw ValidationException::withMessages(['country_code' => __('The selected country is not supported.')]);
        }

        $state = (string) ($addressData['state'] ?? '');
        $allowedRegions = config("addresses.allowed_regions.$country", []);
        if ($state !== '' && $allowedRegions !== [] && ! in_array($state, $allowedRegions, true)) {
            throw ValidationException::withMessages(['state' => __('The selected region is not supported for the chosen country.')]);
        }

        $postal = (string) ($addressData['postal_code'] ?? '');
        $pattern = config("addresses.postal_code_patterns.$country");
        if ($postal !== '' && $pattern !== null && preg_match($pattern, $postal) !== 1) {
            throw ValidationException::withMessages(['postal_code' => __('The postal code format is invalid for the selected country.')]);
        }
    }

    /**
     * Retrieve the allow-listed countries for the modal.
     *
     * @return array<int, string>
     */
    private function allowedCountries(): array
    {
        $configured = config('addresses.allowed_countries', []);

        if ($configured !== []) {
            return $configured;
        }

        return Country::query()
            ->where('is_active', true)
            ->pluck('cca2')
            ->map(static fn (string $code): string => Str::upper($code))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $title = $this->address->id ? __('Update address') : __('Add new address');

        return view('livewire.modals.account.address-form', [
            'title' => $title,
        ]);
    }
}
