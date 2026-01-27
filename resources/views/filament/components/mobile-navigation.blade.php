{{-- Mobile Navigation Component for Filament Admin --}}
<div class="fi-mobile-nav lg:hidden">
    {{-- Mobile Navigation Toggle Button --}}
    <button 
        type="button"
        class="fi-mobile-nav-toggle fixed top-4 left-4 z-50 p-2 rounded-md bg-white shadow-lg border border-gray-200 lg:hidden"
        onclick="toggleMobileNav()"
        aria-label="{{ __('admin.navigation.toggle_menu') }}"
    >
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    {{-- Mobile Navigation Overlay --}}
    <div 
        id="mobile-nav-overlay" 
        class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"
        onclick="closeMobileNav()"
    ></div>

    {{-- Mobile Navigation Menu --}}
    <nav 
        id="mobile-nav-menu"
        class="fixed top-0 left-0 h-full w-80 bg-white shadow-xl transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:hidden overflow-y-auto"
    >
        {{-- Mobile Navigation Header --}}
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-8 h-8">
                <span class="text-lg font-semibold text-gray-900">{{ config('app.name') }}</span>
            </div>
            <button 
                type="button"
                class="p-2 rounded-md text-gray-400 hover:text-gray-600"
                onclick="closeMobileNav()"
                aria-label="{{ __('admin.navigation.close_menu') }}"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Mobile Navigation Items --}}
        <div class="p-4 space-y-2">
            {{-- Dashboard Link --}}
            <a 
                href="{{ route('filament.admin.pages.dashboard') }}"
                class="flex items-center space-x-3 p-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors duration-200"
                onclick="closeMobileNav()"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                </svg>
                <span>{{ __('admin.navigation.dashboard') }}</span>
            </a>

            {{-- Dynamic Navigation Groups --}}
            @php
                $navigationGroups = \App\Enums\NavigationGroup::cases();
            @endphp

            @foreach($navigationGroups as $group)
                <div class="mb-4">
                    <div class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-500 uppercase tracking-wider">
                        <span class="text-{{ $group->getColor() }}-500">
                            @svg($group->getIcon(), 'w-4 h-4')
                        </span>
                        <span>{{ $group->getLabel() }}</span>
                    </div>
                    
                    {{-- Navigation Items for this group would be dynamically loaded here --}}
                    <div class="ml-6 space-y-1">
                        {{-- This would be populated by Filament's navigation system --}}
                        {{-- For now, we'll show placeholder items --}}
                        <a 
                            href="#"
                            class="block p-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors duration-200"
                            onclick="closeMobileNav()"
                        >
                            {{ __('admin.navigation.example_item') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mobile Navigation Footer --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ auth()->user()->name ?? __('admin.navigation.user') }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ auth()->user()->email ?? __('admin.navigation.admin') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                    @csrf
                    <button 
                        type="submit"
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-md transition-colors duration-200"
                        title="{{ __('admin.navigation.logout') }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>
</div>

{{-- Mobile Navigation JavaScript --}}
<script>
function toggleMobileNav() {
    const overlay = document.getElementById('mobile-nav-overlay');
    const menu = document.getElementById('mobile-nav-menu');
    
    if (menu.classList.contains('-translate-x-full')) {
        // Open menu
        overlay.classList.remove('hidden');
        menu.classList.remove('-translate-x-full');
        document.body.style.overflow = 'hidden';
    } else {
        // Close menu
        closeMobileNav();
    }
}

function closeMobileNav() {
    const overlay = document.getElementById('mobile-nav-overlay');
    const menu = document.getElementById('mobile-nav-menu');
    
    overlay.classList.add('hidden');
    menu.classList.add('-translate-x-full');
    document.body.style.overflow = '';
}

// Close mobile nav when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('mobile-nav-menu');
    const toggle = document.querySelector('.fi-mobile-nav-toggle');
    
    if (!menu.contains(event.target) && !toggle.contains(event.target)) {
        closeMobileNav();
    }
});

// Close mobile nav on window resize to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
        closeMobileNav();
    }
});

// Handle escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeMobileNav();
    }
});
</script>

{{-- Mobile Navigation Styles --}}
<style>
.fi-mobile-nav-toggle {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

.fi-mobile-nav-toggle:active {
    transform: scale(0.95);
}

#mobile-nav-menu {
    -webkit-overflow-scrolling: touch;
}

#mobile-nav-menu a {
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}

#mobile-nav-menu a:active {
    background-color: rgba(0, 0, 0, 0.05);
}

@media (max-width: 768px) {
    .fi-mobile-nav-toggle {
        display: block !important;
    }
}

@media (min-width: 1024px) {
    .fi-mobile-nav {
        display: none !important;
    }
}
</style>