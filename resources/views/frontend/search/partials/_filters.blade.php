<div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('messages.frontend) }}</h2>
        @if ($selectedCategory)
            <a
                href="{{ route('frontend.search.index', array_filter(['q' => $query])) }}"
                class="text-sm font-medium text-blue-600 hover:text-blue-500"
            >
                {{ __('frontend.search.reset_filters') }}
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('frontend.search.index') }}" class="space-y-4">
        <input type="hidden" name="q" value="{{ $query }}">

        <div class="space-y-2">
            <label for="search-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('frontend.search.category_label') }}
            </label>
            <select
                id="search-category"
                name="category"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="">{{ __('frontend.search.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategory === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button
            type="submit"
            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
        >
            {{ __('messages.shared) }}
        </button>
    </form>
</div>
