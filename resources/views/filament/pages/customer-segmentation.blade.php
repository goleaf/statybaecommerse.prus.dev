<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Quick Filters / Actions -->
        <x-filament::card>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    wire:click="$set('segmentType', null)"
                    @class([
                        'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-blue-100 text-blue-800' => $segmentType === null,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $segmentType !== null,
                    ])
                >
                    All Customers
                </button>

                <button
                    wire:click="$set('segmentType', 'value')"
                    @class([
                        'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-green-100 text-green-800' => $segmentType === 'value',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $segmentType !== 'value',
                    ])
                >
                    High Value
                </button>

                <button
                    wire:click="$set('segmentType', 'frequency')"
                    @class([
                        'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-amber-100 text-amber-800' => $segmentType === 'frequency',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $segmentType !== 'frequency',
                    ])
                >
                    Frequent Buyers
                </button>

                <button
                    wire:click="$set('segmentType', 'recent')"
                    @class([
                        'px-3 py-2 rounded-lg text-sm font-medium transition-colors',
                        'bg-sky-100 text-sky-800' => $segmentType === 'recent',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200' => $segmentType !== 'recent',
                    ])
                >
                    Recent Orders
                </button>

                <div class="ml-auto">
                    <a
                        href="{{ \App\Filament\Resources\CustomerGroupResource::getUrl() }}"
                        class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-primary-600 text-white hover:bg-primary-700"
                    >
                        Manage Customer Groups
                    </a>
                </div>
            </div>
        </x-filament::card>

        <!-- Customers Table -->
        <x-filament::card>
            {{ $this->table }}
        </x-filament::card>
    </div>
</x-filament-panels::page>

