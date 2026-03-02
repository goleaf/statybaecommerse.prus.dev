<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account;

use App\Models\Address;
use App\Models\Country;
use App\Support\Address\AddressDataSanitizer;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.templates.account')]
final class AddressEdit extends Component
{
    public Address $address;

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

    /** @var array<string, string> */
    public array $countries = [];

    public function mount(Address $address): void
    {
        if ((int) $address->user_id !== (int) Auth::id() || ! (bool) $address->is_active) {
            abort(403, __('messages.you_are_not_authorized_to_modify_this_address'));
        }

        $this->countries = $this->resolvedCountryOptions();
        $this->address = $address;

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
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    protected function rules(): array
    {
        return [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'address_line_1' => ['required', 'string', 'min:3', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city'           => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'string', 'max:20'],
            'country_code'   => ['required', 'string', 'size:2', Rule::in(array_keys($this->countries))],
            'phone'          => ['nullable', 'string', 'max:30'],
            'type'           => ['required', Rule::in(['billing', 'shipping'])],
            'set_as_default' => ['boolean'],
        ];
    }

    public function updateAddress(): void
    {
        $this->validate();

        try {
            $user = Auth::user();
            if (! $user) {
                abort(401, __('messages.unauthorized'));
            }

            $address = $user->addresses()
                ->whereKey($this->address->getKey())
                ->where('is_active', true)
                ->first();

            if (! $address) {
                abort(403, __('messages.you_are_not_authorized_to_modify_this_address'));
            }

            $payload = AddressDataSanitizer::sanitize([
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
                $payload['is_default'] = true;
            }

            if (($payload['is_default'] ?? false) === true) {
                $user->addresses()
                    ->where('type', $this->type)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update($payload);

            Notification::make()
                ->title(__('messages.address_updated_successfully'))
                ->success()
                ->send();

            $this->redirectRoute('account.addresses');
        } catch (Exception) {
            Notification::make()
                ->title(__('messages.error'))
                ->body(__('messages.failed_to_save_address_please_try_again'))
                ->danger()
                ->send();
        }
    }

    /**
     * @return array<string, string>
     */
    private function resolvedCountryOptions(): array
    {
        $translatedCountries = __('addresses.countries');
        $countryOptions = collect(is_array($translatedCountries) ? $translatedCountries : [])
            ->filter(
                static fn ($name, $code): bool => is_string($name) && $name !== '' && is_string($code) && $code !== ''
            )
            ->mapWithKeys(static fn (string $name, string $code): array => [Str::upper($code) => $name]);

        if ($countryOptions->isEmpty()) {
            $countryOptions = Country::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->pluck('name', 'cca2')
                ->mapWithKeys(static fn (string $name, string $code): array => [Str::upper($code) => $name]);
        }

        return $countryOptions
            ->sort()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.pages.account.address-edit')->title(__('frontend.account.addresses.edit_title'));
    }
}
