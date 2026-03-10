@extends('frontend.layouts.app')

@section('title', __('frontend.search.title'))

@include('components.scripts.debounced-search-form')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('frontend.search.title') }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('frontend.search.subtitle') }}
                        </p>
                    </div>
                    <form
                        method="GET"
                        action="{{ route('frontend.search.index') }}"
                        class="grid w-full gap-3 md:max-w-2xl md:flex-1 md:grid-cols-[minmax(0,1fr)_auto]"
                        x-data="debouncedSearchForm({
                            initialQuery: @js($query),
                            delay: 400,
                            minLength: 2,
                            maxLength: 120,
                            autoSubmit: true,
                            allowEmptyManualSubmit: true,
                        })"
                        @submit.prevent="manualSubmit()"
                    >
                        <label for="search-query" class="sr-only">{{ __('frontend.search.search_products_label') }}</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 dark:text-gray-500">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                id="search-query"
                                type="search"
                                name="q"
                                x-ref="queryField"
                                x-model="term"
                                @input="handleInput()"
                                value="{{ old('q', $query) }}"
                                placeholder="{{ __('frontend.search.search_placeholder') }}"
                                class="block w-full min-w-0 rounded-xl border border-gray-200 bg-white py-3 pr-4 pl-12 text-sm text-gray-900 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder:text-gray-500"
                            >
                        </div>
                        <button
                            type="submit"
                            class="inline-flex w-full shrink-0 items-center justify-center whitespace-nowrap rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 md:w-auto"
                        >
                            {{ __('frontend.search.search_action') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,300px)_minmax(0,1fr)] lg:items-start">
                <aside class="lg:sticky lg:top-4 lg:self-start">
                    <livewire:components.category-sidebar />
                </aside>

                @include('frontend.search.partials._results', [
                    'products' => $products,
                    'query' => $query,
                ])
            </div>
        </div>
    </div>
@endsection
