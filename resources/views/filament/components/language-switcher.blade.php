<div class="filament-language-switcher">
    <x-filament::dropdown>
        <x-slot name="trigger">
            <x-filament::button
                                color="gray"
                                icon="heroicon-o-language"
                                size="sm">
                {{ \App\Support\Locales::label(app()->getLocale()) }}
            </x-filament::button>
        </x-slot>

        <x-filament::dropdown.list>
            @foreach (\App\Support\Locales::supported() as $locale)
                <x-filament::dropdown.list.item
                                                :href="\App\Support\Locales::urlForLocale($locale)"
                                                :active="app()->getLocale() === $locale"
                                                icon="heroicon-o-globe-alt">
                    {{ \App\Support\Locales::label($locale) }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
