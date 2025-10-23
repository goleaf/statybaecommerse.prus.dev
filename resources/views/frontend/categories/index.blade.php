@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Shop categories') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Explore the product groups available in our storefront.') }}</p>
        </header>

        <form method="get" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm">
            <label for="search" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Search categories') }}</label>
            <div class="mt-2 flex gap-2">
                <input type="text" id="search" name="search" value="{{ $search }}" class="flex-1 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900" placeholder="{{ __('Search...') }}">
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Search') }}</button>
            </div>
        </form>

        @if ($categories->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-center text-slate-600 dark:text-slate-300">
                {{ __('No categories available at the moment.') }}
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($categories as $category)
                    <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm">
                        <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                            <a class="hover:text-primary-600 dark:hover:text-primary-400" href="{{ route('frontend.categories.show', $category) }}">{{ $category->name }}</a>
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $category->description ?? __('No description provided.') }}</p>
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">{{ trans_choice(':count product|:count products', $category->visible_products_count, ['count' => $category->visible_products_count]) }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
