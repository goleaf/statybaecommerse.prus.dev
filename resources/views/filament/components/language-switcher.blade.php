@php
    $currentLocale = app()->getLocale();
    $availableLocales = ['lt' => 'Lietuvių', 'en' => 'English', 'de' => 'Deutsch'];
@endphp

<div class="filament-language-switcher">
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button
                                color="gray"
                                icon="heroicon-o-language"
                                size="sm">
                {{ $availableLocales[$currentLocale] ?? 'Language' }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($availableLocales as $locale => $name)
                @php
                    $route = request()->route();
                    $targetUrl = null;

                    if ($route && ($name = $route->getName()) && str_starts_with($name, 'localized.')) {
                        $parameters = $route->parameters();
                        $parameters['locale'] = $locale;
                        $targetUrl = route($name, $parameters, true);
                    } elseif (Route::has('localized.home')) {
                        $targetUrl = route('localized.home', ['locale' => $locale], true);
                    }

                    $targetUrl ??= url('/' . ltrim($locale, '/'));
                @endphp
                <x-filament::dropdown.list.item
                                                :href="$targetUrl"
                                                :active="$currentLocale === $locale"
                                                icon="heroicon-o-globe-alt">
                    {{ $name }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
