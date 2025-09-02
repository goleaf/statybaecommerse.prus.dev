    <x-container class="py-10">
        <h1 class="text-2xl font-semibold mb-6">{{ __('Create Discount Preset') }}</h1>

        <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Preset') }}</label>
                <select wire:model="preset" class="w-full border-gray-300" required>
                    <option value="sitewide_percent">{{ __('Sitewide %') }}</option>
                    <option value="sitewide_fixed">{{ __('Sitewide fixed') }}</option>
                    <option value="category_percent">{{ __('Category %') }}</option>
                    <option value="free_shipping_over">{{ __('Free shipping over threshold') }}</option>
                    <option value="first_order_fixed">{{ __('First order fixed') }}</option>
                    <option value="bogo">{{ __('BOGO (Buy X get Y)') }}</option>
                    <option value="tiered_spend">{{ __('Tiered by spend') }}</option>
                </select>
                @error('preset')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
                <input type="text" wire:model="name" class="w-full border-gray-300" required />
                @error('name')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Value') }}</label>
                <input type="number" step="0.01" wire:model="value" class="w-full border-gray-300" />
                @error('value')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Category') }}</label>
                <select wire:model="category_id" class="w-full border-gray-300">
                    <option value="">—</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Threshold (for free shipping/tiered)') }}</label>
                <input type="number" step="0.01" wire:model="threshold" class="w-full border-gray-300" />
            </div>
            <div class="md:col-span-3 flex items-end">
                <button class="px-4 py-2 bg-primary-600 text-white rounded"
                        wire:loading.attr="disabled">{{ __('Create') }}</button>
            </div>
        </form>
    </x-container>
