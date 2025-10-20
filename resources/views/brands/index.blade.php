@extends('frontend.layouts.app')

@section('title', __('Brands'))

@section('content')
    <div class="container mx-auto px-4">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Brands') }}</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ __('Explore trusted manufacturers supplying professional-grade tools and equipment.') }}
                </p>
            </div>
            <form method="GET" action="{{ route('frontend.brands.index') }}" class="flex w-full flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                <div class="flex w-full flex-col sm:w-64">
                    <label for="q" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Search brands') }}</label>
                    <input type="text" id="q" name="q" value="{{ $search }}"
                           class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                           placeholder="{{ __('Search by name…') }}">
                </div>
                <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-5 py-2.5 font-semibold text-white shadow hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500">
                    {{ __('Search') }}
                </button>
            </form>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($brands as $brand)
                <article class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md dark:bg-slate-900/70 dark:ring-slate-800">
                    <a href="{{ route('frontend.brands.show', $brand) }}" class="block space-y-3">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $brand->name }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3">
                            {{ $brand->description ? strip_tags($brand->description) : __('Specialist supplier for construction professionals.') }}
                        </p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ trans_choice('{0}No active products yet|{1}1 active product|[2,*]:count active products', $brand->published_products_count ?? 0, ['count' => $brand->published_products_count ?? 0]) }}
                        </p>
                    </a>
                </article>
            @empty
                <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                    {{ __('Brands will be published soon. Please check back later!') }}
                </p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $brands->withQueryString()->links() }}
        </div>
    </div>
@endsection
