@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-10">
        <nav aria-label="{{ __('Breadcrumb') }}" class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-primary-600 dark:hover:text-primary-400">{{ __('Home') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('frontend.products.index') }}" class="hover:text-primary-600 dark:hover:text-primary-400">{{ __('Products') }}</a>
            <span class="mx-2">/</span>
            <span class="text-slate-700 dark:text-slate-200">{{ $product->name }}</span>
        </nav>

        <header class="space-y-4">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $product->name }}</h1>
            @if ($product->brand)
                <p class="text-slate-600 dark:text-slate-300">{{ __('Brand') }}: <span class="font-medium">{{ $product->brand->name }}</span></p>
            @endif
            <p class="text-2xl font-semibold text-primary-600">{{ number_format((float) $product->price, 2) }} {{ config('app.currency', 'EUR') }}</p>
        </header>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Description') }}</h2>
            <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $product->description ?: __('This product does not have a description yet.') }}</p>
        </section>

        @if ($reviews->isNotEmpty())
            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Latest reviews') }}</h2>
                <ul class="space-y-4">
                    @foreach ($reviews as $review)
                        <li class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                            <div class="flex items-center justify-between text-sm text-slate-500 dark:text-slate-400">
                                <span class="font-medium text-slate-900 dark:text-slate-100">{{ $review->reviewer_name }}</span>
                                <span>{{ __('Rating') }}: {{ $review->rating }}/5</span>
                            </div>
                            <p class="mt-2 text-slate-700 dark:text-slate-300">{{ $review->content ?? __('No additional comments provided.') }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($relatedProducts->isNotEmpty())
            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('You might also like') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($relatedProducts as $related)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                <a class="hover:text-primary-600 dark:hover:text-primary-400" href="{{ route('frontend.products.show', $related) }}">{{ $related->name }}</a>
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ optional($related->brand)->name }}</p>
                            <p class="mt-2 text-slate-700 dark:text-slate-300">{{ number_format((float) $related->price, 2) }} {{ config('app.currency', 'EUR') }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Add a review') }}</h2>
            <form method="post" action="{{ route('frontend.products.add-review', $product) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="rating">{{ __('Rating') }}</label>
                    <select id="rating" name="rating" class="mt-1 w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900">
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="content">{{ __('Comment') }}</label>
                    <textarea id="content" name="content" rows="4" class="mt-1 w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900"></textarea>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Submit review') }}</button>
            </form>
        </section>
    </div>
@endsection
