<?php

declare (strict_types=1);
namespace App\Livewire\Modals\Account;

use App\Actions\CountriesWithZone;
use App\Actions\ZoneSessionManager;
use App\Models\Address;
use App\Models\Country;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
// Legacy Shopper\Core\Enum\AddressType removed - using custom enum
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
 * @property AddressType $type
 * @property int|null $country_id
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $phone_number
 * @property Address|null $address
 * @property Collection $countries
 */
class AddressForm extends ModalComponent
{
    #[Validate('required|string')]
    public ?string $first_name = null;
    #[Validate('required|string')]
    public ?string $last_name = null;
    #[Validate('required|min:3')]
    public ?string $street_address = null;
    #[Validate('nullable|string')]
    public ?string $street_address_plus = null;
    #[Validate('required')]
    public AddressType $type = AddressType::Billing;
    #[Validate('required')]
    public ?int $country_id = null;
    #[Validate('required|string')]
    public ?string $postal_code = null;
    #[Validate('required|string')]
    public ?string $city = null;
    #[Validate('nullable|string')]
    public ?string $phone_number = null;
    public ?Address $address = null;
    public Collection $countries;
    /**
     * Initialize the Livewire component with parameters.
     * @param int|null $addressId
     * @return void
     */
    public function mount(?int $addressId = null): void
    {
        $this->address = $addressId ? Address::query()->findOrFail($addressId) : new Address();
        $this->countries = Country::query()->whereIn(column: 'id', values: (new CountriesWithZone())->handle()->where('zoneId', ZoneSessionManager::getSession()?->zoneId)->pluck('countryId'))->pluck('name', 'id')->filter(fn($label) => filled($label));
        if ($addressId && $this->address->id) {
            $this->fill(array_merge($this->address->toArray(), ['type' => $this->address->type]));
        }
    }
    /**
     * Handle modalMaxWidth functionality with proper error handling.
     * @return string
     */
    public static function modalMaxWidth(): string
    {
        return '2xl';
    }
    /**
     * Handle save functionality with proper error handling.
     * @return void
     */
    public function save(): void
    {
        $this->validate();
        if ($this->address->id) {
            $this->address->update(array_merge($this->validate(), ['user_id' => Auth::id()]));
        } else {
            Address::query()->create(array_merge($this->validate(), ['user_id' => Auth::id()]));
        }
        Notification::make()->title(__('The address has been successfully saved'))->success()->send();
        $this->dispatch('addresses-updated');
        $this->closeModal();
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.modals.account.address-form', ['title' => $this->address->id ? __('Update address') : __('Add new address')]);
    }
}