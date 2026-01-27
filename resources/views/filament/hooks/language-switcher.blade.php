<div>
    <x-filament::dropdown
        maxHeight="250px"
        placement="left-start"
        teleport="true"
    >
        <x-slot name="trigger">
            <div class="p-2 flex items-center justify-start gap-2">
                <x-filament::icon
                    icon="heroicon-c-language"
                    class="mx-1 h-5 w-5 text-gray-500 dark:text-gray-400"
                />
                {{ __('filament::layout.buttons.language_switcher.label') }}
            </div>
        </x-slot>

        <x-filament::dropdown.header
            class="font-semibold"
            color="gray"
            icon="heroicon-c-language"
        >
            {{ __('filament::layout.buttons.language_switcher.label') }}
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            <x-filament::dropdown.list.item
                :color="(app()->getLocale() === 'en') ? 'primary' : null"
                icon="heroicon-m-chevron-right"
                :href="url('lang/en')"
                tag="a"
            >
                🇺🇸 English
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item
                :color="(app()->getLocale() === 'lt') ? 'primary' : null"
                icon="heroicon-m-chevron-right"
                :href="url('lang/lt')"
                tag="a"
            >
                🇱🇹 Lietuvių
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item
                :color="(app()->getLocale() === 'ru') ? 'primary' : null"
                icon="heroicon-m-chevron-right"
                :href="url('lang/ru')"
                tag="a"
            >
                🇷🇺 Русский
            </x-filament::dropdown.list.item>

            <x-filament::dropdown.list.item
                :color="(app()->getLocale() === 'de') ? 'primary' : null"
                icon="heroicon-m-chevron-right"
                :href="url('lang/de')"
                tag="a"
            >
                🇩🇪 Deutsch
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>