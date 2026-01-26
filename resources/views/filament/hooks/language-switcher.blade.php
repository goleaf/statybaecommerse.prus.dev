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
            @foreach (array_intersect_key(config('app.locales', []), array_flip(\App\Support\Locales::supported())) as $locale => $data)
                <x-filament::dropdown.list.item
                    :color="(app()->getLocale() === $locale) ? 'primary' : null"
                    icon="heroicon-m-chevron-right"
                    :href="url('lang/' . $locale)"
                    tag="a"
                >
                    {{ $data['flag'] ?? '' }} {{ $data['native'] }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
