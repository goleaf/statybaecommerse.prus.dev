@php
    $currentLocale = app()->getLocale();
    $supportedLocales = config('app.supported_locales', ['lt', 'en', 'de', 'ru']);
    
    // Ensure supportedLocales is always an array
    if (is_string($supportedLocales)) {
        $supportedLocales = array_map('trim', explode(',', $supportedLocales));
    }
    
    $localeNames = [
        'lt' => 'Lietuvių',
        'en' => 'English',
        'de' => 'Deutsch',
        'ru' => 'Русский',
    ];
    $localeFlags = [
        'lt' => '🇱🇹',
        'en' => '🇺🇸',
        'de' => '🇩🇪',
        'ru' => '🇷🇺',
    ];
@endphp

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition-colors duration-200"
            aria-label="{{ __('Change language') }}"
            aria-expanded="false"
            aria-haspopup="true">
        <span class="text-lg">{{ $localeFlags[$currentLocale] ?? '🌐' }}</span>
        <span class="hidden sm:inline">{{ $localeNames[$currentLocale] ?? strtoupper($currentLocale) }}</span>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none"
             stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-large border border-gray-200 py-2 z-50"
         style="display: none;">
        @foreach ($supportedLocales as $locale)
            @if ($locale !== $currentLocale)
                <a href="{{ route('localized.home', ['locale' => $locale]) ?? url('/?locale=' . $locale) }}"
                   class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 hover:text-blue-600 transition-colors duration-200">
                    <span class="text-lg">{{ $localeFlags[$locale] ?? '🌐' }}</span>
                    <span>{{ $localeNames[$locale] ?? strtoupper($locale) }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
