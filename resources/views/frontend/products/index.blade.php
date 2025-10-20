@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Product catalog') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Browse our latest products and discover new favourites.') }}</p>
        </header>

        <section aria-label="{{ __('Filters') }}" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-6 shadow-sm">
            <div class="flex flex-wrap gap-4 text-sm text-slate-600 dark:text-slate-300">
                <div>
                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ __('Search') }}:</strong>
                    {{ $filters['search'] ?: __('Any') }}
                </div>
                <div>
                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ __('Category') }}:</strong>
                    {{ optional($currentCategory)->name ?: __('All categories') }}
                </div>
                <div>
                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ __('Brand') }}:</strong>
                    {{ optional($currentBrand)->name ?: __('All brands') }}
                </div>
                <div>
                    <strong class="font-medium text-slate-900 dark:text-slate-100">{{ __('Sort by') }}:</strong>
                    {{ str_replace('_', ' ', $filters['sort']) }}
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <aside class="md:col-span-1 space-y-6" aria-label="{{ __('Catalog navigation') }}">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('Categories') }}</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($categories as $category)
                            <li>
                                <a class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300" href="{{ route('frontend.products.index', array_filter(array_merge(request()->query(), ['category' => $category->slug]))) }}">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">{{ __('Brands') }}</h2>
                    <ul class="space-y-2 text-sm">
                        @foreach ($brands as $brand)
                            <li>
                                <a class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300" href="{{ route('frontend.products.index', array_filter(array_merge(request()->query(), ['brand' => $brand->slug]))) }}">
                                    {{ $brand->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            <div class="md:col-span-2">
                @if ($products->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-center text-slate-600 dark:text-slate-300">
                        {{ __('No products matched your filters. Try adjusting the criteria and search again.') }}
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach ($products as $product)
                            <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                                <div class="p-5 space-y-4">
                                    <header class="space-y-1">
                                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                            <a href="{{ route('frontend.products.show', $product) }}" class="hover:text-primary-600 dark:hover:text-primary-400">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ optional($product->brand)->name }}</p>
                                    </header>

                                    <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-3">{{ \Illuminate\Support\Str::limit($product->short_description ?: $product->description, 140) }}</p>

                                    <dl class="text-sm text-slate-600 dark:text-slate-300 grid grid-cols-2 gap-2">
                                        <div>
                                            <dt class="font-medium text-slate-900 dark:text-slate-100">{{ __('Price') }}</dt>
                                            <dd>{{ number_format((float) $product->price, 2) }} {{ config('app.currency', 'EUR') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="font-medium text-slate-900 dark:text-slate-100">{{ __('Reviews') }}</dt>
                                            <dd>{{ $product->reviews_count }}</dd>
                                        </div>
                                    </dl>

                                    <a href="{{ route('frontend.products.show', $product) }}" class="inline-flex items-center justify-center rounded-lg border border-primary-200 dark:border-primary-500 px-4 py-2 text-sm font-medium text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20">
                                        {{ __('View details') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
