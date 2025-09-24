<?php

declare(strict_types=1);

namespace App\Livewire\Modals\Account;

use App\Models\Address;
use App\Models\Country;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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

    #[Validate('required|exists:countries,cca2')]
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
        // Load countries
        $this->countries = Country::where('is_active', true)
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
            $addressData = [
                'user_id' => Auth::id(),
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
            ];

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
                Address::create($addressData);
                $message = __('Address added successfully');
            }

            Notification::make()
                ->title($message)
                ->success()
                ->send();

            $this->dispatch('addresses-updated');
            $this->closeModal();

        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Error'))
                ->body(__('Failed to save address. Please try again.'))
                ->danger()
                ->send();
        }
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
