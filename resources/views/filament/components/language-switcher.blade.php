<div class="filament-language-switcher">
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button
                                color="gray"
                                icon="heroicon-o-language"
                                size="sm">
                {{ $availableLocales[$currentLocale] ?? __('admin.language_switcher.language') }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($availableLocales as $locale => $name)
                <x-filament::dropdown.list.item
                                                :href="$localeLinks[$locale] ?? url('/' . ltrim($locale, '/'))"
                                                :active="$currentLocale === $locale"
                                                icon="heroicon-o-globe-alt">
                    {{ $name }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
