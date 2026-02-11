@php
    use Illuminate\Support\Facades\Route;

    $groups = [
        [
            'label' => __('admin.navigation.products'),
            'icon' => 'heroicon-o-cube',
            'items' => [
                [
                    'route' => 'filament.admin.resources.products.index',
                    'active' => 'filament.admin.resources.products.*',
                    'label' => __('admin.models.products'),
                    'icon' => 'heroicon-o-cube',
                ],
                [
                    'route' => 'filament.admin.resources.categories.index',
                    'active' => 'filament.admin.resources.categories.*',
                    'label' => __('admin.models.categories'),
                    'icon' => 'heroicon-o-tag',
                ],
                [
                    'route' => 'filament.admin.resources.collections.index',
                    'active' => 'filament.admin.resources.collections.*',
                    'label' => __('admin.models.collections'),
                    'icon' => 'heroicon-o-folder',
                ],
                [
                    'route' => 'filament.admin.resources.brands.index',
                    'active' => 'filament.admin.resources.brands.*',
                    'label' => __('admin.navigation.brands'),
                    'icon' => 'heroicon-o-star',
                ],
                [
                    'route' => 'filament.admin.resources.product-features.index',
                    'active' => 'filament.admin.resources.product-features.*',
                    'label' => __('admin.navigation.product_features'),
                    'icon' => 'heroicon-o-tag',
                ],
                [
                    'route' => 'filament.admin.resources.product-images.index',
                    'active' => 'filament.admin.resources.product-images.*',
                    'label' => __('admin.navigation.product_images'),
                    'icon' => 'heroicon-o-photo',
                ],
            ],
        ],
        [
            'label' => __('admin.navigation.product_variants'),
            'icon' => 'heroicon-o-list-bullet',
            'items' => [
                [
                    'route' => 'filament.admin.resources.product-variants.index',
                    'active' => 'filament.admin.resources.product-variants.*',
                    'label' => __('admin.navigation.product_variants'),
                    'icon' => 'heroicon-o-list-bullet',
                ],
                [
                    'route' => 'filament.admin.resources.variant-combinations.index',
                    'active' => 'filament.admin.resources.variant-combinations.*',
                    'label' => __('admin.variant_combinations.navigation_label'),
                    'icon' => 'heroicon-o-squares-2x2',
                ],
            ],
        ],
        [
            'label' => __('admin.inventory_management.title'),
            'icon' => 'heroicon-o-archive-box',
            'items' => [
                [
                    'route' => 'filament.admin.resources.inventory-management.index',
                    'active' => 'filament.admin.resources.inventory-management.*',
                    'label' => __('admin.inventory_management.title'),
                    'icon' => 'heroicon-o-archive-box',
                ],
                [
                    'route' => 'filament.admin.resources.prices.index',
                    'active' => 'filament.admin.resources.prices.*',
                    'label' => __('admin.prices.navigation_label'),
                    'icon' => 'heroicon-o-currency-dollar',
                ],
            ],
        ],
    ];

    $standaloneItems = [
        [
            'route' => 'filament.admin.resources.product-requests.index',
            'active' => 'filament.admin.resources.product-requests.*',
            'label' => __('admin.navigation.product_requests'),
            'icon' => 'heroicon-o-inbox',
        ],
    ];
@endphp

<ul class="fi-topbar-nav-groups">
    @foreach ($standaloneItems as $item)
        @if (Route::has($item['route']))
            <x-filament-panels::topbar.item
                :url="route($item['route'])"
                :active="request()->routeIs($item['active'])"
                :icon="$item['icon']"
            >
                {{ $item['label'] }}
            </x-filament-panels::topbar.item>
        @endif
    @endforeach

    @foreach ($groups as $group)
        @php
            $visibleItems = collect($group['items'])
                ->filter(fn (array $item): bool => Route::has($item['route']))
                ->values();
        @endphp

        @if ($visibleItems->isNotEmpty())
            <x-filament::dropdown placement="bottom-start" teleport>
                <x-slot name="trigger">
                    <x-filament-panels::topbar.item
                        :active="request()->routeIs(collect($group['items'])->pluck('active')->all())"
                        :icon="$group['icon']"
                    >
                        {{ $group['label'] }}
                    </x-filament-panels::topbar.item>
                </x-slot>

                <x-filament::dropdown.list>
                    @foreach ($visibleItems as $item)
                        <x-filament::dropdown.list.item
                            :href="route($item['route'])"
                            :icon="$item['icon']"
                            :color="request()->routeIs($item['active']) ? 'primary' : 'gray'"
                            tag="a"
                        >
                            {{ $item['label'] }}
                        </x-filament::dropdown.list.item>
                    @endforeach
                </x-filament::dropdown.list>
            </x-filament::dropdown>
        @endif
    @endforeach
</ul>
