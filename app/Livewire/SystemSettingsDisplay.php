<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\SystemSettingsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * SystemSettingsDisplay
 *
 * Livewire component for SystemSettingsDisplay with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $group
 * @property bool $showPublicOnly
 * @property string $search
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
        'group' => ['except' => 'general'],
        'showPublicOnly' => ['except' => false],
        'search' => ['except' => ''],
    ];

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $settingsService = app(SystemSettingsService::class);
        $settings = $this->showPublicOnly
            ? $settingsService->getPublicSettings()
            : $settingsService->getSettingsByGroup($this->group);

        $searchTerm = $this->search;

        if ($searchTerm !== '') {
            $settings = array_filter(
                $settings,
                static function ($value, string $key) use ($searchTerm): bool {
                    $normalizedKey = stripos((string) $key, $searchTerm) !== false;
                    $normalizedValue = is_scalar($value) && stripos((string) $value, $searchTerm) !== false;

                    return $normalizedKey || $normalizedValue;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        return view('livewire.system-settings-display', ['settings' => $settings, 'groups' => $this->getAvailableGroups()]);
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
}
