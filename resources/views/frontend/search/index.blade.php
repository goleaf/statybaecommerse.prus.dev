@extends('frontend.layouts.app')

@section('title', __('frontend.search.results'))

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('frontend.search.results') }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('frontend.search.help') }}
                        </p>
                    </div>
                    <form method="GET" action="{{ route('frontend.search.index') }}" class="flex w-full md:w-auto gap-2">
                        <label for="search-query" class="sr-only">{{ __('frontend.search.placeholder') }}</label>
                        <input
                            id="search-query"
                            type="search"
                            name="q"
                            value="{{ old('q', $query) }}"
                            placeholder="{{ __('frontend.search.placeholder') }}"
                            class="flex-1 md:w-72 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500"
                        >
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
                        >
                            {{ __('shared.search') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[280px_minmax(0,1fr)] gap-6">
                @include('frontend.search.partials._filters', [
                    'categories' => $categories,
                    'selectedCategory' => request('category'),
                    'query' => $query,
                ])

                @include('frontend.search.partials._results', [
                    'products' => $products,
                    'query' => $query,
                ])
            </div>
        </div>
    </div>
@endsection
