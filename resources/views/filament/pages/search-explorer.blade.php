@php use Illuminate\Support\Str; @endphp

<x-filament-panels::page>
    <div class="space-y-8">
        <form wire:submit.prevent="submit" class="space-y-4">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                    {{ __('Search') }}
                </x-filament::button>
            </div>
        </form>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Products') }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        ({{ $results['products']['total'] ?? 0 }})
                    </span>
                </h2>
                <x-filament::section>
                    <div class="space-y-4">
                        @forelse($results['products']['items'] ?? [] as $product)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $product['title'] ?? '' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $product['subtitle'] ?? '' }}
                                        </div>
                                    </div>
                                    <div class="text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product['formatted_price'] ?? '' }}
                                    </div>
                                </div>
                                @if(! empty($product['description']))
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        {{ Str::limit($product['description'], 160) }}
                                    </div>
                                @endif
                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Score') }}: {{ number_format((float) ($product['relevance_score'] ?? $product['score'] ?? 0), 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No products found.') }}
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>
            </div>

            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Categories') }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        ({{ $results['categories']['total'] ?? 0 }})
                    </span>
                </h2>
                <x-filament::section>
                    <div class="space-y-4">
                        @forelse($results['categories']['items'] ?? [] as $category)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $category['title'] ?? '' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $category['subtitle'] ?? '' }}
                                </div>
                                @if(! empty($category['description']))
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        {{ Str::limit($category['description'], 160) }}
                                    </div>
                                @endif
                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Score') }}: {{ number_format((float) ($category['score'] ?? 0), 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No categories found.') }}
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>
            </div>

            <div class="space-y-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Brands') }}
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        ({{ $results['brands']['total'] ?? 0 }})
                    </span>
                </h2>
                <x-filament::section>
                    <div class="space-y-4">
                        @forelse($results['brands']['items'] ?? [] as $brand)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $brand['title'] ?? '' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $brand['subtitle'] ?? '' }}
                                </div>
                                @if(! empty($brand['description']))
                                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                        {{ Str::limit($brand['description'], 160) }}
                                    </div>
                                @endif
                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Score') }}: {{ number_format((float) ($brand['score'] ?? 0), 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No brands found.') }}
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
