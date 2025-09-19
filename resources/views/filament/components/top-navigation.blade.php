@php
    use App\Enums\NavigationGroup;
    use Illuminate\Support\Facades\Route;
    $navigationGroups = NavigationGroup::ordered();
    $user = auth()->user();
    $isAdmin = $user?->is_admin ?? false;

    if (!function_exists('canAccessGroup')) {
        function canAccessGroup($group)
        {
            $user = auth()->user();
            if (!$user) {
                return false;
            }

            if ($group->requiresPermission()) {
                return $user->can($group->getPermission());
            }

            if ($group->isAdminOnly()) {
                return $user->is_admin || $user->hasAnyRole(['admin', 'Admin']);
            }

            return true;
        }
    }
@endphp

<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo/Brand -->
            <div class="flex items-center">
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center space-x-2">
                    <x-heroicon-o-cube class="h-8 w-8 text-primary-600" />
                    <span class="text-xl font-bold text-gray-900">{{ __('admin_panel') }}</span>
                </a>
            </div>

            <!-- Main Navigation -->
            <nav class="hidden md:flex space-x-8">
                @foreach ($navigationGroups as $group)
                    @if (canAccessGroup($group))
                        <div class="relative group">
                            <button
                                    class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 transition-colors duration-200">
                                <x-dynamic-component :component="'heroicon-o-' . $group->icon()" class="h-4 w-4" />
                                <span>{{ $group->label() }}</span>
                                <x-heroicon-o-chevron-down class="h-3 w-3" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                 class="absolute left-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-1">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $group->label() }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">{{ $group->description() }}</p>
                                    </div>

                                    @if ($group->value === 'Products')
                                        @if (Route::has('filament.admin.resources.products.index'))
                                            <a href="{{ route('filament.admin.resources.products.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-cube class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.products') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.categories.index'))
                                            <a href="{{ route('filament.admin.resources.categories.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-tag class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.categories') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('filament.admin.resources.brands.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-star class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.brands') }}
                                        </a>
                                        <a href="{{ route('filament.admin.resources.collections.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-folder class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.collections') }}
                                        </a>
                                    @elseif($group->value === 'Orders')
                                        <a href="{{ route('filament.admin.resources.orders.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-shopping-bag class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.orders') }}
                                        </a>
                                        <a href="{{ route('filament.admin.resources.cart-items.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-shopping-cart class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.cart_items') }}
                                        </a>
                                    @elseif($group->value === 'Users')
                                        <a href="{{ route('filament.admin.resources.users.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-users class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.users') }}
                                        </a>
                                        <a href="{{ route('filament.admin.resources.customer-groups.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-user-group class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.customer_groups') }}
                                        </a>
                                    @elseif($group->value === 'Inventory')
                                        @if (Route::has('filament.admin.resources.stocks.index'))
                                            <a href="{{ route('filament.admin.resources.stocks.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-archive-box class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.stock') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.product-variants.index'))
                                            <a href="{{ route('filament.admin.resources.product-variants.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-cube-transparent class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.product_variants') }}
                                            </a>
                                        @endif
                                    @elseif($group->value === 'Locations')
                                        @if (Route::has('filament.admin.resources.locations.index'))
                                            <a href="{{ route('filament.admin.resources.locations.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-map-pin class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.locations') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.countries.index'))
                                            <a href="{{ route('filament.admin.resources.countries.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-globe-alt class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.countries') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.zones.index'))
                                            <a href="{{ route('filament.admin.resources.zones.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-map class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.zones') }}
                                            </a>
                                        @endif
                                    @elseif($group->value === 'Marketing')
                                        @if (Route::has('filament.admin.resources.campaigns.index'))
                                            <a href="{{ route('filament.admin.resources.campaigns.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-megaphone class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.campaigns') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.discount-codes.index'))
                                            <a href="{{ route('filament.admin.resources.discount-codes.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-ticket class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.discount_codes') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.coupons.index'))
                                            <a href="{{ route('filament.admin.resources.coupons.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-gift class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.coupons') }}
                                            </a>
                                        @endif
                                    @elseif($group->value === 'Analytics')
                                        <a href="{{ route('filament.admin.resources.analytics-events.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-chart-bar class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.analytics_events') }}
                                        </a>
                                    @elseif($group->value === 'Reports')
                                        <a href="{{ route('filament.admin.resources.reports.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-document-chart-bar class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.reports') }}
                                        </a>
                                    @elseif($group->value === 'Content')
                                        <a href="{{ route('filament.admin.resources.news.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-newspaper class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.news') }}
                                        </a>
                                        <a href="{{ route('filament.admin.resources.posts.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-document-text class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.posts') }}
                                        </a>
                                        <a href="{{ route('filament.admin.resources.legal.index') }}"
                                           class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-document class="h-4 w-4 mr-3" />
                                            {{ __('admin.models.legal') }}
                                        </a>
                                    @elseif($group->value === 'System')
                                        @if (Route::has('filament.admin.resources.system-settings.index'))
                                            <a href="{{ route('filament.admin.resources.system-settings.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.system_settings') }}
                                            </a>
                                        @endif
                                        @if (Route::has('filament.admin.resources.activity-logs.index'))
                                            <a href="{{ route('filament.admin.resources.activity-logs.index') }}"
                                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <x-heroicon-o-clock class="h-4 w-4 mr-3" />
                                                {{ __('admin.models.activity_logs') }}
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
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
                        <span>{{ app()->getLocale() === 'lt' ? 'LT' : 'EN' }}</span>
                    </button>
                </div>

                <!-- Notifications -->
                <button class="relative p-2 text-gray-400 hover:text-gray-500">
                    <x-heroicon-o-bell class="h-5 w-5" />
                    <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
                </button>

                <!-- User Dropdown -->
                <div class="relative">
                    <button
                            class="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                        <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">{{ substr($user->name ?? 'A', 0, 1) }}</span>
                        </div>
                        <span>{{ $user->name ?? 'Admin' }}</span>
                        <x-heroicon-o-chevron-down class="h-3 w-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
