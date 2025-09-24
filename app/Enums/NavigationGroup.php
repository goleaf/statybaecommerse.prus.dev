<?php

declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

/**
 * NavigationGroup
 *
 * Enumeration defining a set of named constants with type safety.
 */
enum NavigationGroup: string
{
    case Referral = 'Referral System';
    case Products = 'Products';
    case Orders = 'Orders';
    case Users = 'Users';
    case Settings = 'Settings';
    case Analytics = 'Analytics';
    case Content = 'Content';
    case ContentManagement = 'Content Management';
    case System = 'System';
    case Marketing = 'Marketing';
    case Inventory = 'Inventory';
    case Reports = 'Reports';
    case Locations = 'Locations';
    case Discounts = 'Discounts';
    case Campaigns = 'Campaigns';
    case News = 'News';

    public function label(): string
    {
        return match ($this) {
            self::Referral => __('translations.nav_group_referral'),
            self::Products => __('translations.nav_group_products'),
            self::Orders => __('translations.nav_group_orders'),
            self::Users => __('translations.nav_group_users'),
            self::Settings => __('translations.nav_group_settings'),
            self::Analytics => __('translations.nav_group_analytics'),
            self::Content => __('translations.nav_group_content'),
            self::ContentManagement => __('translations.nav_group_content_management'),
            self::System => __('translations.nav_group_system'),
            self::Marketing => __('translations.nav_group_marketing'),
            self::Inventory => __('translations.nav_group_inventory'),
            self::Reports => __('translations.nav_group_reports'),
            self::Locations => __('translations.nav_group_locations'),
            self::Discounts => __('translations.nav_group_discounts'),
            self::Campaigns => __('translations.nav_group_campaigns'),
            self::News => __('news.title'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Referral => __('translations.nav_group_referral_description'),
            self::Products => __('translations.nav_group_products_description'),
            self::Orders => __('translations.nav_group_orders_description'),
            self::Users => __('translations.nav_group_users_description'),
            self::Settings => __('translations.nav_group_settings_description'),
            self::Analytics => __('translations.nav_group_analytics_description'),
            self::Content => __('translations.nav_group_content_description'),
            self::ContentManagement => __('translations.nav_group_content_management_description'),
            self::System => __('translations.nav_group_system_description'),
            self::Marketing => __('translations.nav_group_marketing_description'),
            self::Inventory => __('translations.nav_group_inventory_description'),
            self::Reports => __('translations.nav_group_reports_description'),
            self::Locations => __('translations.nav_group_locations_description'),
            self::Discounts => __('translations.nav_group_discounts_description'),
            self::Campaigns => __('translations.nav_group_campaigns_description'),
            self::News => __('news.navigation_group'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Referral => 'gift',
            self::Products => 'cube',
            self::Orders => 'shopping-bag',
            self::Users => 'users',
            self::Settings => 'cog-6-tooth',
            self::Analytics => 'chart-bar',
            self::Content => 'document-text',
            self::ContentManagement => 'document-duplicate',
            self::System => 'computer-desktop',
            self::Marketing => 'megaphone',
            self::Inventory => 'archive-box',
            self::Reports => 'document-chart-bar',
            self::Locations => 'globe-alt',
            self::Discounts => 'tag',
            self::Campaigns => 'megaphone',
            self::News => 'newspaper',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Referral => 'purple',
            self::Products => 'blue',
            self::Orders => 'green',
            self::Users => 'indigo',
            self::Settings => 'gray',
            self::Analytics => 'yellow',
            self::Content => 'pink',
            self::ContentManagement => 'purple',
            self::System => 'red',
            self::Marketing => 'orange',
            self::Inventory => 'teal',
            self::Reports => 'cyan',
            self::Locations => 'emerald',
            self::Discounts => 'rose',
            self::Campaigns => 'orange',
            self::News => 'blue',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::Products => 1,
            self::Orders => 2,
            self::Users => 3,
            self::Inventory => 4,
            self::Locations => 5,
            self::Discounts => 6,
            self::Campaigns => 7,
            self::News => 8,
            self::Marketing => 9,
            self::Analytics => 10,
            self::Reports => 11,
            self::Content => 12,
            self::ContentManagement => 13,
            self::Referral => 14,
            self::Settings => 15,
            self::System => 16,
        };
    }

    public function isCore(): bool
    {
        return match ($this) {
            self::Products, self::Orders, self::Users, self::Inventory, self::Locations, self::Discounts, self::Campaigns, self::News, self::ContentManagement => true,
            default => false,
        };
    }

    public function isAdminOnly(): bool
    {
        return match ($this) {
            self::System, self::Analytics, self::Reports => true,
            default => false,
        };
    }

    public function isPublic(): bool
    {
        return match ($this) {
            self::Products, self::Content, self::ContentManagement, self::Marketing, self::Locations, self::Discounts, self::Campaigns, self::News => true,
            default => false,
        };
    }

    public function requiresPermission(): bool
    {
        return match ($this) {
            self::Users, self::Settings, self::System, self::Analytics, self::Reports => true,
            default => false,
        };
    }

    public function getPermission(): string
    {
        return match ($this) {
            self::Users => 'manage_users',
            self::Settings => 'manage_settings',
            self::System => 'manage_system',
            self::Analytics => 'view_analytics',
            self::Reports => 'view_reports',
            default => 'view_'.strtolower($this->value),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn ($case) => [$case->value => ['label' => $case->label(), 'description' => $case->description(), 'icon' => $case->icon(), 'color' => $case->color(), 'is_core' => $case->isCore(), 'is_admin_only' => $case->isAdminOnly(), 'is_public' => $case->isPublic(), 'requires_permission' => $case->requiresPermission(), 'permission' => $case->getPermission()]])->toArray();
    }

    public static function core(): Collection
    {
        return collect(self::cases())->filter(fn ($case) => $case->isCore());
    }

    public static function adminOnly(): Collection
    {
        return collect(self::cases())->filter(fn ($case) => $case->isAdminOnly());
    }

    public static function public(): Collection
    {
        return collect(self::cases())->filter(fn ($case) => $case->isPublic());
    }

    public static function withPermissions(): Collection
    {
        return collect(self::cases())->filter(fn ($case) => $case->requiresPermission());
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())->sortBy('priority');
    }

    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())->first(fn ($case) => $case->label() === $label);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())->map(fn ($case) => $case->label())->toArray();
    }

    public function toArray(): array
    {
        return ['value' => $this->value, 'label' => $this->label(), 'description' => $this->description(), 'icon' => $this->icon(), 'color' => $this->color(), 'priority' => $this->priority(), 'is_core' => $this->isCore(), 'is_admin_only' => $this->isAdminOnly(), 'is_public' => $this->isPublic(), 'requires_permission' => $this->requiresPermission(), 'permission' => $this->getPermission()];
    }
}
