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
    $hiddenAdminLocales = collect(['ru']);

    $availableLocales = $directoryLocales
        ->merge($jsonLocales)
        ->map(static fn (string $locale): string => strtolower(trim($locale)))
        ->filter(static fn (string $locale): bool => $locale !== '')
        ->unique()
        ->when($supportedLocales->isNotEmpty(), static fn ($locales) => $locales->intersect($supportedLocales))
        ->sort()
        ->values();

    if ($availableLocales->isEmpty() && $supportedLocales->isNotEmpty()) {
        $availableLocales = $supportedLocales;
    }

    $availableLocales = $availableLocales
        ->map(static fn (mixed $locale): string => strtolower(trim((string) $locale)))
        ->filter(static fn (string $locale): bool => $locale !== '' && preg_match('/^[a-z\-_]+$/i', $locale) === 1)
        ->reject(static fn (string $locale): bool => $hiddenAdminLocales->contains($locale))
        ->values();

    $currentLocale = strtolower((string) app()->getLocale());

    if (
        ! $availableLocales->contains($currentLocale)
        && $currentLocale !== ''
        && ! $hiddenAdminLocales->contains($currentLocale)
    ) {
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

@if ($availableLocales->count() > 1)
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
                        $targetLocale = strtolower(trim((string) $locale));
                    @endphp

                    @continue($targetLocale === '')

                    @php
                        $isActive = $targetLocale === $currentLocale;
                        $switchUrl = request()->fullUrlWithQuery([]);

                        if (\Illuminate\Support\Facades\Route::has('language.switch')) {
                            try {
                                $switchUrl = route('language.switch', [
                                    'locale' => $targetLocale,
                                    'redirect_to' => url()->full(),
                                    'context' => 'admin',
                                ]);
                            } catch (\Throwable) {
                                $switchUrl = request()->fullUrlWithQuery([]);
                            }
                        }
                    @endphp

                    <x-filament::dropdown.list.item
                        :href="$switchUrl"
                        :color="$isActive ? 'primary' : 'gray'"
                        tag="a"
                    >
                        {{ $localeLabels[$targetLocale] ?? strtoupper($targetLocale) }} ({{ strtoupper($targetLocale) }})
                    </x-filament::dropdown.list.item>
                @endforeach
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    </div>
@endif
