@extends('components.layouts.base')

@section('title', __('Products'))

@section('content')
    <x-container class="py-8 space-y-8">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Products') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Browse our catalogue of available products.') }}</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[16rem_auto]">
            <aside class="space-y-4">
                <form method="get" action="{{ route('frontend.products.index') }}" class="space-y-4">
                    <x-input label="{{ __('Search') }}" name="q" value="{{ $filters['search'] ?? '' }}" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Categories') }}</label>
                        <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">{{ __('All categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Brands') }}</label>
                        <select name="brand" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">{{ __('All brands') }}</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->slug }}" @selected(($filters['brand'] ?? '') === $brand->slug)>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <x-button type="submit" class="w-full">{{ __('Apply filters') }}</x-button>
                </form>
            </aside>

            <section class="space-y-4">
                <p class="text-sm text-gray-600">
                    {{ trans_choice(':count product|:count products', $products->total(), ['count' => $products->total()]) }}
                </p>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse ($products as $product)
                        <article class="rounded-lg border border-gray-200 bg-white shadow-sm">
                            <a href="{{ route('frontend.products.show', $product) }}" class="block">
                                @php($media = $product->getFirstMedia(config('media.storage.collection_name')))
                                @if ($media)
                                    <img src="{{ $media->getFullUrl() }}" alt="{{ $product->name }}" class="h-48 w-full rounded-t-lg object-cover">
                                @else
                                    <div class="h-48 w-full rounded-t-lg bg-gray-100"></div>
                                @endif

                                <div class="space-y-2 p-4">
                                    <h2 class="text-lg font-medium text-gray-900">{{ $product->trans('name') ?? $product->name }}</h2>
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ $product->trans('short_description') ?? $product->short_description }}</p>
                                    <p class="text-base font-semibold text-primary-600">
                                        {{ optional($product->prices->first())->formatted ?? \Illuminate\Support\Number::currency((float) ($product->price ?? 0), current_currency(), app()->getLocale()) }}
                                    </p>
                                </div>
                            </a>
                        </article>
                    @empty
                        <div class="col-span-full rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                            {{ __('No products matched your filters.') }}
                        </div>
                    @endforelse
                </div>

                {{ $products->links() }}
            </section>
        </div>
    </x-container>
@endsection
