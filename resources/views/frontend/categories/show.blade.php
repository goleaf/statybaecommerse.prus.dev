@extends('components.layouts.base')

@section('title', $category->trans('name') ?? $category->name)

@section('content')
    <x-container class="py-8 space-y-8">
        <header class="space-y-2">
            <p class="text-sm text-gray-500">{{ __('Category') }}</p>
            <h1 class="text-3xl font-semibold text-gray-900">{{ $category->trans('name') ?? $category->name }}</h1>
            @if ($category->description)
                <p class="text-gray-600">{{ $category->description }}</p>
            @endif
        </header>

        @if ($childCategories->isNotEmpty())
            <section class="space-y-4">
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('Subcategories') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($childCategories as $child)
                        <a href="{{ route('frontend.categories.show', $child) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-primary-300">
                            <p class="font-medium text-gray-900">{{ $child->name }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('Products') }}</h2>
                <span class="text-sm text-gray-500">{{ trans_choice(':count product|:count products', $products->total(), ['count' => $products->total()]) }}</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($products as $product)
                    <a href="{{ route('frontend.products.show', $product) }}" class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        @php($media = $product->getFirstMedia(config('media.storage.collection_name')))
                        @if ($media)
                            <img src="{{ $media->getFullUrl() }}" alt="{{ $product->name }}" class="h-40 w-full rounded-t-lg object-cover">
                        @else
                            <div class="h-40 w-full rounded-t-lg bg-gray-100"></div>
                        @endif
                        <div class="space-y-1 p-3">
                            <h3 class="font-medium text-gray-900">{{ $product->trans('name') ?? $product->name }}</h3>
                            <p class="text-sm text-gray-600">{{ optional($product->brand)->name }}</p>
                            <p class="text-sm font-semibold text-primary-600">
                                {{ optional($product->prices->first())->formatted ?? \Illuminate\Support\Number::currency((float) ($product->price ?? 0), current_currency(), app()->getLocale()) }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                        {{ __('No products in this category yet.') }}
                    </div>
                @endforelse
            </div>

            {{ $products->links() }}
        </section>
    </x-container>
@endsection
