{{-- Enhanced Global Search with Autocomplete --}}
<div class="hidden lg:block lg:ml-6">
    <livewire:components.live-search 
        :max-results="8"
        :search-types="['products', 'categories', 'brands']"
        :enable-suggestions="true"
        :enable-recent-searches="true"
        :enable-popular-searches="false"
        placeholder="{{ __('Search products…') }}"
        class="w-72"
    />
    
    @if (request()->filled('q'))
        <p class="mt-1 text-xs text-gray-400">{{ __('Showing results for ":q"', ['q' => request('q')]) }}</p>
    @endif
</div>
