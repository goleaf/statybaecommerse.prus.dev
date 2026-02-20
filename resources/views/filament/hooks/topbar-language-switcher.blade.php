@php
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Str;

    $directoryLocales = collect(File::directories(lang_path()))
        ->map(static fn (string $path): string => basename($path));

    $jsonLocales = collect(File::files(lang_path()))
        ->map(static fn (\SplFileInfo $file): string => $file->getFilename())
        ->filter(static fn (string $filename): bool => Str::endsWith($filename, '.json'))
        ->map(static fn (string $filename): string => Str::before($filename, '.json'));

    $supportedLocalesConfig = config('app.supported_locales', []);
    $supportedLocales = collect(is_array($supportedLocalesConfig) ? $supportedLocalesConfig : explode(',', (string) $supportedLocalesConfig))
        ->map(static fn ($locale): string => strtolower(trim((string) $locale)))
        ->filter(static fn (string $locale): bool => $locale !== '')
        ->unique()
        ->values();

    $availableLocales = $directoryLocales
        ->merge($jsonLocales)
        ->map(static fn (string $locale): string => strtolower(trim($locale)))
        ->filter(static fn (string $locale): bool => $locale !== '')
        ->unique()
        ->when($supportedLocales->isNotEmpty(), static fn ($locales) => $locales->intersect($supportedLocales))
        ->sort()->values();

    if ($availableLocales->isEmpty() && $supportedLocales->isNotEmpty()) {
        $availableLocales = $supportedLocales;
    }

    $availableLocales = $availableLocales
        ->values();

    $currentLocale = strtolower((string) app()->getLocale());

    if (! $availableLocales->contains($currentLocale) && $currentLocale !== '') {
        $availableLocales = $availableLocales->prepend($currentLocale)->unique()->values();
    }

    $configuredLocales = config('app.locales', []);

    $localeLabels = $availableLocales->mapWithKeys(function (string $locale) use ($configuredLocales): array {
        $label = data_get($configuredLocales, "{$locale}.native")
            ?? data_get($configuredLocales, "{$locale}.name")
            ?? strtoupper($locale);

        return [$locale => (string) $label];
    });
@endphp

@if ($availableLocales->isNotEmpty())
    <div class="fi-topbar-language-switcher ms-2">
        <x-filament::dropdown placement="bottom-end" teleport>
            <x-slot name="trigger">
                <x-filament-panels::topbar.item icon="heroicon-o-language">
                    {{ strtoupper($currentLocale) }}
                </x-filament-panels::topbar.item>
            </x-slot>

            <x-filament::dropdown.list>
                @foreach ($availableLocales as $locale)
                    @php
                        $isActive = $locale === $currentLocale;
                    @endphp

                    @php
                        $switchUrl = \Illuminate\Support\Facades\Route::has('language.switch')
                            ? route('language.switch', [
                                'locale'      => $locale,
                                'redirect_to' => url()->full(),
                                'context'     => 'admin',
                            ])
                            : request()->fullUrlWithQuery(['locale' => $locale]);
                    @endphp

                    <x-filament::dropdown.list.item
                        :href="$switchUrl"
                        :color="$isActive ? 'primary' : 'gray'"
                        tag="a"
                    >
                        {{ $localeLabels[$locale] }} ({{ strtoupper($locale) }})
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
