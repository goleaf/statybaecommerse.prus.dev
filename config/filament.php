<?php

declare(strict_types=1);

use App\Enums\NavigationGroup as AdminNavigationGroup;

return [
    'navigation' => [
        'groups' => [
            [
                'key' => 'dashboard',
                'label' => 'admin.navigation.dashboard',
                'icon' => 'heroicon-o-home',
            ],
            [
                'key' => 'commerce',
                'label' => 'admin.navigation.commerce',
                'icon' => 'heroicon-o-shopping-bag',
            ],
            [
                'key' => AdminNavigationGroup::Products->value,
                'label' => 'admin.navigation.products',
                'icon' => 'heroicon-o-cube',
            ],
            [
                'key' => AdminNavigationGroup::Marketing->value,
                'label' => 'admin.navigation.marketing',
                'icon' => 'heroicon-o-megaphone',
            ],
            [
                'key' => AdminNavigationGroup::Content->value,
                'label' => 'admin.navigation.content',
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'key' => AdminNavigationGroup::Analytics->value,
                'label' => 'admin.navigation.analytics',
                'icon' => 'heroicon-o-chart-bar',
            ],
            [
                'key' => AdminNavigationGroup::System->value,
                'label' => 'admin.navigation.system',
                'icon' => 'heroicon-o-cog-6-tooth',
            ],
            [
                'key' => 'recommendation-system',
                'label' => 'translations.recommendation_system',
                'icon' => 'heroicon-o-sparkles',
            ],
        ],
        'resources' => [
            App\Filament\Resources\SystemSettingResource::class,
            App\Filament\Resources\CustomerManagementResource::class,
            App\Filament\Resources\AddressResource::class,
        ],
        'pages' => [
            App\Filament\Pages\Dashboard::class,
            App\Filament\Pages\SliderAnalytics::class,
            App\Filament\Pages\SliderManagement::class,
            App\Filament\Pages\InventoryManagement::class,
            App\Filament\Pages\AdvancedReports::class,
            App\Filament\Pages\UserImpersonation::class,
            App\Filament\Pages\ObservabilityDashboard::class,
        ],
    ],
];
