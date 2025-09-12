<?php declare(strict_types=1);

namespace App\Livewire;

use App\Services\SystemSettingsService;
use Livewire\Component;

final class SystemSettingsDisplay extends Component
{
    public string $group = 'general';
    public bool $showPublicOnly = false;
    public string $search = '';

    protected $queryString = [
        'group' => ['except' => 'general'],
        'showPublicOnly' => ['except' => false],
        'search' => ['except' => ''],
    ];

    public function render()
    {
        $settingsService = app(SystemSettingsService::class);
        
        $settings = $this->showPublicOnly 
            ? $settingsService->getPublicSettings()
            : $settingsService->getSettingsByGroup($this->group);

        // Filter by search if provided
        if ($this->search) {
            $settings = array_filter($settings, function ($value, $key) {
                return stripos($key, $this->search) !== false || 
                       stripos($value, $this->search) !== false;
            }, ARRAY_FILTER_USE_BOTH);
        }

        return view('livewire.system-settings-display', [
            'settings' => $settings,
            'groups' => $this->getAvailableGroups(),
        ]);
    }

    public function updatedGroup()
    {
        $this->reset('search');
    }

    public function updatedShowPublicOnly()
    {
        $this->reset('search');
    }

    public function updatedSearch()
    {
        // Search is handled in render method
    }

    private function getAvailableGroups(): array
    {
        return [
            'general' => __('system_settings.general'),
            'ecommerce' => __('system_settings.ecommerce'),
            'email' => __('system_settings.email'),
            'payment' => __('system_settings.payment'),
            'shipping' => __('system_settings.shipping'),
            'seo' => __('system_settings.seo'),
            'security' => __('system_settings.security'),
            'api' => __('system_settings.api'),
            'appearance' => __('system_settings.appearance'),
            'notifications' => __('system_settings.notifications'),
        ];
    }
}
