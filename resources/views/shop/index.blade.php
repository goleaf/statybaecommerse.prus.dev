<x-layouts.base title="{{ __('messages.frontend') }}">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <header class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('messages.frontend') }}</h1>
            @auth
                @can('view system')
                    <a class="text-sm text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 underline"
                       href="{{ route('filament.admin.pages.dashboard') }}">{{ __('messages.frontend') }}</a>
                @endcan
            @endauth
            <x-language-switcher />
        </header>

        @if ($products->isEmpty())
            <div
                 class="text-center p-12 text-gray-500 border border-dashed border-gray-300 rounded-xl dark:border-white/10 dark:text-gray-400">
                {{ __('messages.frontend') }}
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($products as $product)
                    <div
                         class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        @php
                            $productUrl = \Illuminate\Support\Facades\Route::has('frontend.products.show')
                                ? route('frontend.products.show', $product)
                                : (\Illuminate\Support\Facades\Route::has('products.show')
                                    ? route('products.show', $product)
                                    : '#');
                        @endphp
                        @php($media = $product->getFirstMedia(config('media.storage.collection_name')))
                        <a href="{{ $productUrl }}" class="block">
                            <img src="{{ $media ? $media->getFullUrl() : asset('images/placeholder-product.jpg') }}" alt="{{ $product->trans('name') ?? $product->name }}"
                                 class="w-full h-40 object-cover rounded-lg">
                        </a>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $product->trans('name') ?? $product->name }}</h3>
                        @php($price = $product->prices->first())
                        <div class="text-green-600 font-semibold">{{ $price?->formatted ?? '—' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.base>
