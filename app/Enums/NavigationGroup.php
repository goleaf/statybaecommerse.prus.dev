<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum NavigationGroup: string
{
    case UserManagement = 'user-management';
    case ContentManagement = 'content-management';
    case Ecommerce = 'ecommerce';
    case System = 'system';
    case Analytics = 'analytics';
    case Marketing = 'marketing';
    case Reports = 'reports';
    case Settings = 'settings';
    case Search = 'search';

    // Additional cases used in resources
    case Users = 'users';
    case Products = 'products';
    case Orders = 'orders';
    case Inventory = 'inventory';
    case Content = 'content';
    case Locations = 'locations';
    case Discounts = 'discounts';
    case Campaigns = 'campaigns';
    case News = 'news';
    case Referral = 'referral';

    public function getLabel(): string
    {
        return match ($this) {
            self::UserManagement    => __('navigation.navigation_groups.user_management'),
            self::ContentManagement => __('navigation.navigation_groups.content_management'),
            self::Ecommerce         => __('navigation.navigation_groups.ecommerce'),
            self::System            => __('navigation.navigation_groups.system'),
            self::Analytics         => __('navigation.navigation_groups.analytics'),
            self::Marketing         => __('navigation.navigation_groups.marketing'),
            self::Reports           => __('navigation.navigation_groups.reports'),
            self::Settings          => __('navigation.navigation_groups.settings'),
            self::Search            => __('navigation.navigation_groups.search'),
            self::Users             => __('navigation.navigation_groups.users'),
            self::Products          => __('navigation.navigation_groups.products'),
            self::Orders            => __('navigation.navigation_groups.orders'),
            self::Inventory         => __('navigation.navigation_groups.inventory'),
            self::Content           => __('navigation.navigation_groups.content'),
            self::Locations         => __('navigation.navigation_groups.locations'),
            self::Discounts         => __('navigation.navigation_groups.discounts'),
            self::Campaigns         => __('navigation.navigation_groups.campaigns'),
            self::News              => __('navigation.navigation_groups.news'),
            self::Referral          => __('navigation.navigation_groups.referral'),
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function description(): ?string
    {
        return null;
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::UserManagement    => 'heroicon-o-users',
            self::ContentManagement => 'heroicon-o-document-text',
            self::Ecommerce         => 'heroicon-o-shopping-cart',
            self::System            => 'heroicon-o-cog-6-tooth',
            self::Analytics         => 'heroicon-o-chart-bar',
            self::Marketing         => 'heroicon-o-megaphone',
            self::Reports           => 'heroicon-o-document-chart-bar',
            self::Settings          => 'heroicon-o-adjustments-horizontal',
            self::Search            => 'heroicon-o-magnifying-glass',
            self::Users             => 'heroicon-o-users',
            self::Products          => 'heroicon-o-cube',
            self::Orders            => 'heroicon-o-shopping-bag',
            self::Inventory         => 'heroicon-o-archive-box',
            self::Content           => 'heroicon-o-document-text',
            self::Locations         => 'heroicon-o-globe-alt',
            self::Discounts         => 'heroicon-o-tag',
            self::Campaigns         => 'heroicon-o-megaphone',
            self::News              => 'heroicon-o-newspaper',
            self::Referral          => 'heroicon-o-gift',
        };
    }

    public function icon(): string
    {
        return $this->getIcon();
    }

    public function priority(): int
    {
        return match ($this) {
            self::UserManagement    => 10,
            self::ContentManagement => 20,
            self::Ecommerce         => 30,
            self::System            => 90,
            self::Analytics         => 40,
            self::Marketing         => 50,
            self::Reports           => 60,
            self::Settings          => 80,
            self::Search            => 70,
            self::Users             => 11,
            self::Products          => 31,
            self::Orders            => 32,
            self::Inventory         => 33,
            self::Content           => 21,
            self::Locations         => 34,
            self::Discounts         => 35,
            self::Campaigns         => 51,
            self::News              => 22,
            self::Referral          => 52,
        };
    }

    public function color(): string
    {
        return $this->getColor();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::UserManagement    => 'blue',
            self::ContentManagement => 'green',
            self::Ecommerce         => 'orange',
            self::System            => 'gray',
            self::Analytics         => 'purple',
            self::Marketing         => 'pink',
            self::Reports           => 'indigo',
            self::Settings          => 'slate',
            self::Search            => 'cyan',
            self::Users             => 'blue',
            self::Products          => 'blue',
            self::Orders            => 'green',
            self::Inventory         => 'teal',
            self::Content           => 'green',
            self::Locations         => 'emerald',
            self::Discounts         => 'rose',
            self::Campaigns         => 'orange',
            self::News              => 'blue',
            self::Referral          => 'purple',
        };
    }

    public function isCore(): bool
    {
        return in_array($this, [self::System, self::Settings, self::UserManagement]);
    }

    public function isAdminOnly(): bool
    {
        return $this === self::System || $this === self::Settings;
    }

    public function isPublic(): bool
    {
        return ! $this->isAdminOnly();
    }

    public function requiresPermission(): bool
    {
        return $this->isAdminOnly();
    }

    public function getPermission(): ?string
    {
        return $this->requiresPermission() ? 'access_' . str_replace('-', '_', $this->value) : null;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => ['label' => $case->label(), 'description' => $case->description()]])
            ->all();
    }

    public static function core(): Collection
    {
        return collect(self::cases())->filter(fn (self $case) => $case->isCore());
    }

    public static function adminOnly(): Collection
    {
        return collect(self::cases())->filter(fn (self $case) => $case->isAdminOnly());
    }

    public static function public(): Collection
    {
        return collect(self::cases())->filter(fn (self $case) => $case->isPublic());
    }

    public static function withPermissions(): Collection
    {
        return collect(self::cases())->filter(fn (self $case) => $case->requiresPermission());
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())->sortBy(fn (self $case) => $case->priority());
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) {
                return $case;
            }
        }

        return null;
    }

    public static function values(): array
    {
        return collect(self::cases())->map(fn (self $case) => $case->value)->all();
    }

    public static function labels(): array
    {
        return collect(self::cases())->map(fn (self $case) => $case->label())->all();
    }
}
