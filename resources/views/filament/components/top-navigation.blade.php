@php
    use Illuminate\Support\Facades\Route;
    $user = auth()->user();
    $isAdmin = $user?->is_admin ?? false;
    $notificationStreamUrl = $user?->getAuthIdentifier()
        ? route('api.notifications.stream', ['user' => $user->getAuthIdentifier()])
        : null;
    $productMenu = [
        [
            'route' => 'filament.admin.resources.products.index',
            'label' => __('admin.models.products'),
            'icon'  => 'cube',
        ],
        [
            'route' => 'filament.admin.resources.categories.index',
            'label' => __('admin.models.categories'),
            'icon'  => 'tag',
        ],
        [
            'route' => 'filament.admin.resources.collections.index',
            'label' => __('admin.models.collections'),
            'icon'  => 'folder',
        ],
        [
            'route' => 'filament.admin.resources.product-variants.index',
            'label' => __('admin.navigation.product_variants'),
            'icon'  => 'list-bullet',
        ],
        [
            'route' => 'filament.admin.resources.variant-combinations.index',
            'label' => __('admin.variant_combinations.navigation_label'),
            'icon'  => 'squares-2x2',
        ],
        [
            'route' => 'filament.admin.resources.inventory-management.index',
            'label' => __('admin.inventory_management.title'),
            'icon'  => 'archive-box',
        ],
        [
            'route' => 'filament.admin.resources.brands.index',
            'label' => __('admin.navigation.brands'),
            'icon'  => 'tag',
        ],
        [
            'route' => 'filament.admin.resources.product-images.index',
            'label' => __('admin.navigation.product_images'),
            'icon'  => 'photo',
        ],
        [
            'route' => 'filament.admin.resources.product-features.index',
            'label' => __('admin.navigation.product_features'),
            'icon'  => 'tag',
        ],
        [
            'route' => 'filament.admin.resources.product-requests.index',
            'label' => __('admin.navigation.product_requests'),
            'icon'  => 'inbox',
        ],
        [
            'route' => 'filament.admin.resources.discounts.index',
            'label' => __('admin.discounts.navigation_label'),
            'icon'  => 'receipt-percent',
        ],
        [
            'route' => 'filament.admin.resources.prices.index',
            'label' => __('admin.prices.navigation_label'),
            'icon'  => 'currency-dollar',
        ],
    ];
@endphp

<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo/Brand -->
            <div class="flex items-center">
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center space-x-2">
                    <x-heroicon-o-cube class="h-8 w-8 text-primary-600" />
                    <span class="text-xl font-bold text-gray-900">{{ __('admin.navigation.admin_panel') }}</span>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="hidden md:flex flex-wrap gap-2">
                @foreach ($productMenu as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs($item['route']) ? 'text-primary-600 bg-gray-50' : 'text-gray-700 hover:text-primary-600 hover:bg-gray-50' }} transition-colors duration-200">
                            <x-heroicon-o-{{ $item['icon'] }} class="h-4 w-4" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="relative">
                    <button
                            class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <x-heroicon-o-language class="h-4 w-4" />
                        <span>{{ app()->getLocale() === 'lt' ? __('admin.language_switcher.short.lt') : __('admin.language_switcher.short.en') }}</span>
                    </button>
                </div>

                <!-- Notifications -->
                <div
                    @if($notificationStreamUrl)
                        data-notification-stream-url="{{ $notificationStreamUrl }}"
                    @endif
                    class="relative"
                >
                    <button
                            type="button"
                            aria-label="{{ __('messages.admin') }}"
                            class="relative p-2 text-gray-400 hover:text-gray-500"
                    >
                        <x-heroicon-o-bell class="h-5 w-5" />
                        @if($notificationStreamUrl)
                            <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
                        @endif
                    </button>
                </div>

                <!-- User Dropdown -->
                <div class="relative">
                    <button
                            class="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">{{ substr($user->name ?? 'A', 0, 1) }}</span>
                        </div>
                        <span>{{ $user->name ?? __('messages.admin') }}</span>
                        <x-heroicon-o-chevron-down class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
