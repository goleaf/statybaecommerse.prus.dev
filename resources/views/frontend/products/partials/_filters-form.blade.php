<h2 class="text-lg font-semibold text-gray-900">{{ __('ui.search_catalogue') }}</h2>
<form method="get" class="mt-4 space-y-4">
    <div>
        <label for="search-mobile" class="block text-sm font-medium text-gray-700">{{ __('messages.search') }}</label>
        <div class="mt-1 flex rounded-full border border-gray-200 bg-gray-50 px-4 py-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500">
            <input id="search-mobile" name="q" type="search" value="{{ $searchTerm }}" placeholder="{{ __('ui.search_products') }}" class="w-full border-none bg-transparent text-sm focus:outline-none" />
            <x-untitledui-search-sm class="h-5 w-5 text-gray-400" />
        </div>
    </div>

    <div>
        <label for="filter-mobile" class="block text-sm font-medium text-gray-700">{{ __('ui.quick_filter') }}</label>
        <div class="mt-2 space-y-2">
            @foreach ($availableFilters as $key => $label)
                <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                    <span>{{ $label }}</span>
                    <input id="filter-mobile" type="radio" name="filter" value="{{ $key }}" @checked($activeFilter === $key) class="h-4 w-4 text-indigo-600" />
                </label>
            @endforeach
            <label class="flex items-center justify-between rounded-2xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">
                <span>{{ __('ui.clear_filters') }}</span>
                <input type="radio" name="filter" value="" @checked(! $activeFilter) class="h-4 w-4 text-indigo-600" />
            </label>
        </div>
    </div>

    <button type="submit" class="w-full rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
        {{ __('ui.apply_filters') }}
    </button>
</form>
