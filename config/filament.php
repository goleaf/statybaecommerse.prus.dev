<?php

declare(strict_types=1);

use App\Enums\NavigationGroup as AdminNavigationGroup;

return [
    'navigation' => [
        'groups' => [
            [
                'key'   => 'dashboard',
                'label' => 'admin.navigation.dashboard',
                'icon'  => 'heroicon-o-home',
            ],
            [
                'key'   => 'commerce',
                'label' => 'admin.navigation.commerce',
                'icon'  => 'heroicon-o-shopping-bag',
            ],
            [
                'key'   => AdminNavigationGroup::Products->value,
                'label' => 'admin.navigation.products',
                'icon'  => 'heroicon-o-cube',
            ],
            [
                'key'   => AdminNavigationGroup::Marketing->value,
                'label' => 'admin.navigation.marketing',
                'icon'  => 'heroicon-o-megaphone',
            ],
            [
                'key'   => AdminNavigationGroup::Content->value,
                'label' => 'admin.navigation.content',
                'icon'  => 'heroicon-o-document-text',
            ],
            [
                'key'   => AdminNavigationGroup::System->value,
                'label' => 'admin.navigation.system',
                'icon'  => 'heroicon-o-cog-6-tooth',
            ],
        ],
        'resources' => [
            App\Filament\Resources\SystemSettingResource::class,
        ],
        'pages' => [
            App\Filament\Pages\Dashboard::class,
        ],
    ],
];
