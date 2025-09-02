<div>
    <div class="flex items-center gap-6">
        @if (Route::has('brand.index'))
            <x-link :href="route('brand.index', ['locale' => app()->getLocale()])"
                    class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_brands') }}</x-link>
        @endif

        <x-link :href="route('locations.index', ['locale' => app()->getLocale()])"
                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_locations') }}</x-link>

        <x-link :href="route('search.index', ['locale' => app()->getLocale()])"
                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_search') }}</x-link>

        <x-link :href="route('cart.index', ['locale' => app()->getLocale()])"
                class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_cart') }}</x-link>

        @auth
            @can('view system')
                <x-link :href="route('admin.discounts.presets')"
                        class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_admin') }}</x-link>
            @endcan
            @can('view orders')
                <x-link :href="route('exports.index')"
                        class="text-sm font-medium text-gray-700 hover:text-gray-800">{{ __('nav_exports') }}</x-link>
            @endcan
        @endauth
    </div>

    @php
        $categoryFeature = config('shopper.features.category') ?? null;
        $featureEnabled =
            $categoryFeature instanceof \App\Support\FeatureState
                ? $categoryFeature === \App\Support\FeatureState::Enabled
                : (is_string($categoryFeature)
                    ? strtolower($categoryFeature) === strtolower(\App\Support\FeatureState::Enabled->value)
                    : (bool) $categoryFeature);
    @endphp

    @if ($featureEnabled && isset($categories) && count($categories) && Route::has('category.show'))
        <div class="hidden items-center gap-x-6 lg:flex">
            @foreach ($categories as $category)
                @php
                    $slug = method_exists($category, 'trans')
                        ? $category->trans('slug') ?? $category->slug
                        : $category->slug;
                    $name = method_exists($category, 'trans')
                        ? $category->trans('name') ?? $category->name
                        : $category->name;
                @endphp
                <x-nav.item :href="route('category.show', ['locale' => app()->getLocale(), 'slug' => $slug])">{{ $name }}</x-nav.item>
            @endforeach
        </div>
    @endif
</div>
