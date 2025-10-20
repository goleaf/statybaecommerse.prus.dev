@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $category->name }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ $category->description ?? __('This category does not have a description yet.') }}</p>
        </header>

        <div class="flex items-center justify-between bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-4">
            <div class="text-sm text-slate-600 dark:text-slate-300">
                {{ trans_choice(':count product|:count products', $products->total(), ['count' => $products->total()]) }}
            </div>
            <form method="get">
                <label for="sort" class="sr-only">{{ __('Sort') }}</label>
                <select id="sort" name="sort" class="rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900" onchange="this.form.submit()">
                    <option value="latest" @selected($sort === 'latest')>{{ __('Newest first') }}</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>{{ __('Price: Low to high') }}</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>{{ __('Price: High to low') }}</option>
                    <option value="name_asc" @selected($sort === 'name_asc')>{{ __('Name A–Z') }}</option>
                    <option value="name_desc" @selected($sort === 'name_desc')>{{ __('Name Z–A') }}</option>
                </select>
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-center text-slate-600 dark:text-slate-300">
                {{ __('No products available in this category right now.') }}
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <article class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-5 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            <a class="hover:text-primary-600 dark:hover:text-primary-400" href="{{ route('frontend.products.show', $product) }}">{{ $product->name }}</a>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ optional($product->brand)->name }}</p>
                        <p class="mt-3 text-base font-semibold text-primary-600">{{ number_format((float) $product->price, 2) }} {{ config('app.currency', 'EUR') }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
