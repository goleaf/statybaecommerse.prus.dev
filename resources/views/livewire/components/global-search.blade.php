<?php
use function Livewire\Volt\{on, state};

state([
    'q' => request('q'),
]);

?>

<form
      method="GET"
      action="{{ route('home') }}"
      class="hidden lg:block lg:ml-6">
    <label for="q" class="sr-only">{{ __('Search') }}</label>
    <div class="relative">
        <input
               type="search"
               id="q"
               name="q"
               value="{{ $q }}"
               placeholder="{{ __('Search products…') }}"
               class="block w-72 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500" />
        <button type="submit" class="absolute inset-y-0 right-0 p-2 text-gray-400 hover:text-gray-500">
            <span class="sr-only">{{ __('Search') }}</span>
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                 aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </button>
    </div>
    @if (request()->filled('q'))
        <p class="mt-1 text-xs text-gray-400">{{ __('Showing results for ":q"', ['q' => request('q')]) }}</p>
    @endif
</form>
