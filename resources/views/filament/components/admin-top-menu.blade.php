@php
    use App\Enums\NavigationGroup;
    $navigationGroups = NavigationGroup::ordered();
    $user = auth()->user();
    $isAdmin = $user?->is_admin ?? false;
@endphp

<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14">
            <!-- Quick Navigation Links -->
            <div class="flex items-center space-x-6">
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-primary-600">
                    <x-heroicon-o-home class="h-4 w-4" />
                    <span>{{ __('admin.navigation.dashboard') }}</span>
                </a>
                
                @foreach($navigationGroups->take(6) as $group)
                    @if($group->isCore() || $group->isPublic())
                        <div class="relative group">
                            <button class="flex items-center space-x-1 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50">
                                <x-dynamic-component :component="'heroicon-o-' . $group->icon()" class="h-4 w-4" />
                                <span>{{ $group->label() }}</span>
                            </button>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="flex items-center space-x-1">
                    <button class="flex items-center space-x-1 px-2 py-1 rounded text-xs font-medium text-gray-600 hover:text-primary-600">
                        <x-heroicon-o-language class="h-3 w-3" />
                        <span>{{ app()->getLocale() === 'lt' ? 'LT' : 'EN' }}</span>
                    </button>
                </div>

                <!-- Notifications -->
                <button class="relative p-2 text-gray-400 hover:text-gray-500">
                    <x-heroicon-o-bell class="h-4 w-4" />
                    <span class="absolute -top-1 -right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- User Info -->
                <div class="flex items-center space-x-2">
                    <div class="h-6 w-6 rounded-full bg-primary-600 flex items-center justify-center">
                        <span class="text-xs font-medium text-white">{{ substr($user->name ?? 'A', 0, 1) }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $user->name ?? 'Admin' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
