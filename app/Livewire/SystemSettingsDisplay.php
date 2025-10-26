<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Data\Storefront\System\SystemSettingEntryData;
use App\Services\SystemSettingsService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTags;
use App\Support\Cache\TagAwareCache;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use JsonException;
use Livewire\Component;
use Traversable;

/**
 * SystemSettingsDisplay
 *
 * Livewire component for SystemSettingsDisplay with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string                              $group
 * @property bool                                $showPublicOnly
 * @property string                              $search
 * @property array<string, array<string, mixed>> $queryString
 */
final class SystemSettingsDisplay extends Component
{
    public string $group = 'general';

    public bool $showPublicOnly = false;

    public string $search = '';

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $queryString = [
        'group'          => ['except' => 'general'],
        'showPublicOnly' => ['except' => false],
        'search'         => ['except' => ''],
    ];

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $settingsService = app(SystemSettingsService::class);

        /** @var array<int, array{key:string, value:mixed}> $payload */
        $payload = TagAwareCache::remember(
            CacheKeys::systemSettingsGroup($this->group, $this->showPublicOnly),
            now()->addMinutes(5),
            function () use ($settingsService): array {
                $source = $this->showPublicOnly
                    ? $settingsService->getPublicSettings()
                    : $settingsService->getSettingsByGroup($this->group);

                if ($source instanceof Traversable) {
                    $source = iterator_to_array($source);
                }

                if (! is_array($source)) {
                    $source = [];
                }

                return collect($source)
                    ->map(static fn ($value, string $key): array => (new SystemSettingEntryData($key, $value))->toArray())
                    ->values()
                    ->all();
            },
            [CacheTags::settings()]
        );

        $entries = array_map(
            static fn (array $entry): SystemSettingEntryData => SystemSettingEntryData::fromArray($entry),
            $payload,
        );

        $searchTerm = trim($this->search);

        if ($searchTerm !== '') {
            $needle = Str::lower($searchTerm);
            $entries = array_values(array_filter(
                $entries,
                fn (SystemSettingEntryData $entry): bool => $this->entryMatchesSearch($entry, $needle)
            ));
        }

        return view('livewire.system-settings-display', [
            'settings' => $entries,
            'groups'   => $this->getAvailableGroups(),
        ]);
    }

    /**
     * Handle updatedGroup functionality with proper error handling.
     */
    public function updatedGroup(): void
    {
        $this->reset('search');
    }

    /**
     * Handle updatedShowPublicOnly functionality with proper error handling.
     */
    public function updatedShowPublicOnly(): void
    {
        $this->reset('search');
    }

    /**
     * Handle updatedSearch functionality with proper error handling.
     */
    public function updatedSearch(): void
    {
        // Search is handled in render method
    }

    /**
     * Handle getAvailableGroups functionality with proper error handling.
     */
    /**
     * @return array<string, string>
     */
    private function getAvailableGroups(): array
    {
        return ['general' => __('system_settings.general'), 'ecommerce' => __('system_settings.ecommerce'), 'email' => __('system_settings.email'), 'payment' => __('system_settings.payment'), 'shipping' => __('system_settings.shipping'), 'seo' => __('system_settings.seo'), 'security' => __('system_settings.security'), 'api' => __('system_settings.api'), 'appearance' => __('system_settings.appearance'), 'notifications' => __('system_settings.notifications')];
    }

    /**
     * Normalise mixed setting values for display within the Blade template.
     */
    public function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value) || is_object($value)) {
            try {
                return (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } catch (JsonException) {
                return '[]';
            }
        }

        if ($value === null) {
            return 'null';
        }

        return (string) $value;
    }

    /**
     * Determine if the provided setting entry should be visible when a search term is applied.
     */
    private function entryMatchesSearch(SystemSettingEntryData $entry, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        if (Str::contains(Str::lower($entry->key), $needle)) {
            return true;
        }

        $value = $entry->value;

        if (is_scalar($value) || $value === null) {
            return Str::contains(Str::lower((string) ($value ?? '')), $needle);
        }

        try {
            $encoded = json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            $encoded = '';
        }

        return Str::contains(Str::lower($encoded), $needle);
    }
}
