@php
    $isPaginator = $products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $totalResults = $isPaginator ? $products->total() : $products->count();
@endphp

<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('frontend.search.results') }}
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ trans_choice('frontend.search.result_count', $totalResults, ['count' => $totalResults]) }}
                @if (! empty($query))
                    <span class="text-gray-500 dark:text-gray-400">{{ __('frontend.search.for_query', ['query' => $query]) }}</span>
                @endif
            </p>
        </div>

        @if ($isPaginator && $totalResults > 0)
            <span class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ __('frontend.search.pagination_label') }}
            </span>
        @endif
    </div>

    <div class="px-6 py-8">
        @if (empty($query))
            <div class="text-center text-gray-600 dark:text-gray-400">
                <p class="text-lg font-medium">{{ __('frontend.search.help') }}</p>
                <p class="mt-2 text-sm">{{ __('frontend.search.placeholder') }}</p>
            </div>
        @elseif ($totalResults === 0)
            <div class="text-center text-gray-600 dark:text-gray-400">
                <p class="text-lg font-medium">
                    {{ __('frontend.search.no_results_for_query', ['query' => $query]) }}
                </p>
                <p class="mt-2 text-sm">{{ __('frontend.search.try_different_search') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    @include('frontend.search.partials._product-card', ['product' => $product])
                @endforeach
            </div>

            @if ($isPaginator)
                <div class="mt-8">
                    {{ $products->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
