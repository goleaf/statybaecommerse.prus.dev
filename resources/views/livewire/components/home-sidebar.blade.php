<aside class="hidden lg:block sticky top-24 space-y-6">
    <livewire:components.category-accordion-menu />

    <div class="rounded-lg border border-gray-200 bg-white p-4">
        <h3 class="mb-3 text-base font-semibold text-gray-900">{{ __('translations.quick_links') }}</h3>
        <div class="space-y-2">
            <a href="{{ route('products.index') }}"
               class="block text-sm text-gray-700 hover:text-indigo-600 hover:underline">
                {{ __('translations.all_products') }}
            </a>
            <a href="{{ route('brands.index') }}"
               class="block text-sm text-gray-700 hover:text-indigo-600 hover:underline">
                {{ __('translations.all_brands') }}
            </a>
            <a href="{{ route('collections.index') }}"
               class="block text-sm text-gray-700 hover:text-indigo-600 hover:underline">
                {{ __('translations.collections') }}
            </a>
        </div>
    </div>
</aside>
