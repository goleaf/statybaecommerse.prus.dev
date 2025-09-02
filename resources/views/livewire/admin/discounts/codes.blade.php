<x-container class="py-10">
    <h1 class="text-2xl font-semibold mb-6">{{ __('Codes for') }}: {{ $discount->code ?? $discount->id }}</h1>

    <form wire:submit.prevent="generate" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('Prefix') }}</label>
            <input type="text" wire:model.defer="prefix" class="w-full border-gray-300" maxlength="16" />
            @error('prefix')
                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('Length') }}</label>
            <input type="number" wire:model.defer="length" class="w-full border-gray-300" min="4"
                    max="32"
                    required />
            @error('length')
                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('Quantity') }}</label>
            <input type="number" wire:model.defer="quantity" class="w-full border-gray-300" min="1"
                    max="5000" required />
            @error('quantity')
                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="flex items-end">
            <button class="px-4 py-2 bg-primary-600 text-white rounded"
                    wire:loading.attr="disabled">{{ __('Generate') }}</button>
        </div>
    </form>

    <div class="mb-4">
        @php $latest = $this->latestCsvPath(); @endphp
        @if ($latest)
            <a href="{{ route('admin.discounts.codes.download', ['discountId' => $discount->id]) }}"
                class="text-primary-600 hover:underline">{{ __('Download latest CSV') }}</a>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2 pr-4">{{ __('Code') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codes as $code)
                    <tr class="border-b">
                        <td class="py-2 pr-4">{{ $code }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-container>
