<div>
    <div class="relative">
        <select wire:change="setCurrency($event.target.value)" class="rounded-md border-gray-300 text-sm">
            @foreach ($this->currencies as $currency)
                <option value="{{ data_get($currency, 'code') }}"
                        @selected(data_get($currency, 'active'))>
                    {{ data_get($currency, 'symbol') }} {{ data_get($currency, 'code') }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="hidden lg:ml-8 lg:flex">
        <button
                onclick="Livewire.dispatch('openPanel', { component: 'modals.zone-selector' })"
                type="button"
                class="flex items-center gap-2 text-gray-700 hover:text-gray-800">
            @if ($this->countryFlag)
                <img src="{{ $this->countryFlag }}" alt="country flag" class="block h-auto w-5 shrink-0" />
            @endif

            <span class="block text-sm font-medium">{{ current_currency() }}</span>
            <span class="sr-only">, {{ __('change currency') }}</span>
        </button>
    </div>
</div>
