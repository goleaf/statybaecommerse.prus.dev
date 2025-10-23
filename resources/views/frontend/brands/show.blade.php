@extends('frontend.layouts.app')

@section('title', $brand->name)

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-10 px-6">
            <nav class="text-sm text-gray-500">
                <ol class="flex flex-wrap items-center gap-2">
                    @foreach($breadcrumbs as $crumb)
                        <li class="flex items-center gap-2">
                            @if(! $loop->last)
                                <a href="{{ $crumb['url'] }}" class="hover:text-indigo-600">{{ $crumb['label'] }}</a>
                                <span aria-hidden="true">/</span>
                            @else
                                <span class="text-gray-700">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>

            @php
                $totalProducts = method_exists($products, 'total') ? $products->total() : $products->count();
            @endphp
            <header class="space-y-4 rounded-2xl bg-white p-8 shadow">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                            <span class="text-xl font-semibold">{{ \Illuminate\Support\Str::upper(mb_substr($brand->name, 0, 2)) }}</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Brand') }}</span>
                            <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ $brand->name }}</h1>
                            @if($brand->description)
                                <p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($brand->description), 220) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col items-start gap-2 text-sm text-gray-600">
                        <p>{{ __('Products available:') }} <span class="font-semibold text-gray-900">{{ $totalProducts }}</span></p>
                        @if($brand->website)
                            <a href="{{ $brand->website }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-500">
                                <span class="font-semibold">{{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L12 12M19.5 4.5L9 4.5C6.51472 4.5 4.5 6.51472 4.5 9V15C4.5 17.4853 6.51472 19.5 9 19.5H15C17.4853 19.5 19.5 17.4853 19.5 15V12"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <form method="get" action="{{ route('frontend.brands.show', $brand) }}" class="grid gap-4 rounded-xl bg-white p-6 shadow-sm lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:items-end">
                <div>
                    <label for="q" class="text-sm font-medium text-gray-700">{{ __('Search within brand') }}</label>
                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ $activeFilters['search'] ?? request('q') }}"
                        placeholder="{{ __('Search products...') }}"
                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                </div>
                <div>
                    <label for="sort" class="text-sm font-medium text-gray-700">{{ __('Sort by') }}</label>
                    <select
                        id="sort"
                        name="sort"
                        class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    >
                        @foreach($availableSorts as $key => $label)
                            <option value="{{ $key }}" @selected(($activeFilters['sort'] ?? request('sort', 'latest')) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap justify-end gap-3 lg:col-span-2">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                        {{ __('Apply filters') }}
                    </button>
                    <a href="{{ route('frontend.brands.show', $brand) }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>

            <section>
                @include('frontend.products.partials.grid', ['products' => $products, 'emptyMessage' => __('No products available for this brand just yet.')])
            </section>
        </div>
    </div>
@endsection
