<?php

declare (strict_types=1);
namespace App\Enums;

use Illuminate\Support\Collection;
/**
 * UserRole
 * 
 * Enumeration defining a set of named constants with type safety.
 */
enum UserRole : string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case CUSTOMER = 'customer';
    case GUEST = 'guest';
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('translations.user_role_super_admin'),
            self::ADMIN => __('translations.user_role_admin'),
            self::MANAGER => __('translations.user_role_manager'),
            self::EDITOR => __('translations.user_role_editor'),
            self::CUSTOMER => __('translations.user_role_customer'),
            self::GUEST => __('translations.user_role_guest'),
        };
    }
    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => __('translations.user_role_super_admin_description'),
            self::ADMIN => __('translations.user_role_admin_description'),
            self::MANAGER => __('translations.user_role_manager_description'),
            self::EDITOR => __('translations.user_role_editor_description'),
            self::CUSTOMER => __('translations.user_role_customer_description'),
            self::GUEST => __('translations.user_role_guest_description'),
        };
    }
    public function icon(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'heroicon-o-shield-check',
            self::ADMIN => 'heroicon-o-shield-exclamation',
            self::MANAGER => 'heroicon-o-user-group',
            self::EDITOR => 'heroicon-o-pencil-square',
            self::CUSTOMER => 'heroicon-o-user',
            self::GUEST => 'heroicon-o-user-circle',
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'red',
            self::ADMIN => 'purple',
            self::MANAGER => 'blue',
            self::EDITOR => 'green',
            self::CUSTOMER => 'indigo',
            self::GUEST => 'gray',
        };
    }
    public function level(): int
    {
        return match ($this) {
            self::SUPER_ADMIN => 100,
            self::ADMIN => 90,
            self::MANAGER => 70,
            self::EDITOR => 50,
            self::CUSTOMER => 10,
            self::GUEST => 0,
        };
    }
    public function priority(): int
    {
        return match ($this) {
            self::SUPER_ADMIN => 1,
            self::ADMIN => 2,
            self::MANAGER => 3,
            self::EDITOR => 4,
            self::CUSTOMER => 5,
            self::GUEST => 6,
        };
    }
    public function isAdmin(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            default => false,
        };
    }
    public function isStaff(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER, self::EDITOR => true,
            default => false,
        };
    }
    public function isCustomer(): bool
    {
        return match ($this) {
            self::CUSTOMER, self::GUEST => true,
            default => false,
        };
    }
    public function canAccessAdmin(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER, self::EDITOR => true,
            default => false,
        };
    }
    public function canManageUsers(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            default => false,
        };
    }
    public function canManageProducts(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER, self::EDITOR => true,
            default => false,
        };
    }
    public function canManageOrders(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER => true,
            default => false,
        };
    }
    public function canManageSettings(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN => true,
            default => false,
        };
    }
    public function canViewAnalytics(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER => true,
            default => false,
        };
    }
    public function canManageContent(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER, self::EDITOR => true,
            default => false,
        };
    }
    public function canManageInventory(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER => true,
            default => false,
        };
    }
    public function canManageMarketing(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER => true,
            default => false,
        };
    }
    public function canViewReports(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN, self::ADMIN, self::MANAGER => true,
            default => false,
        };
    }
    public function canManageSystem(): bool
    {
        return match ($this) {
            self::SUPER_ADMIN => true,
            default => false,
        };
    }
    public function permissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['manage_users', 'manage_products', 'manage_orders', 'manage_settings', 'view_analytics', 'manage_content', 'manage_inventory', 'manage_marketing', 'view_reports', 'manage_system', 'manage_referrals'],
            self::ADMIN => ['manage_users', 'manage_products', 'manage_orders', 'manage_settings', 'view_analytics', 'manage_content', 'manage_inventory', 'manage_marketing', 'view_reports', 'manage_referrals'],
            self::MANAGER => ['manage_products', 'manage_orders', 'view_analytics', 'manage_content', 'manage_inventory', 'manage_marketing', 'view_reports', 'manage_referrals'],
            self::EDITOR => ['manage_products', 'manage_content'],
            self::CUSTOMER => ['view_own_orders', 'manage_own_profile', 'view_own_referrals'],
            self::GUEST => [],
        };
    }
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions());
    }
    public function hasHigherLevelThan(UserRole $role): bool
    {
        return $this->level() > $role->level();
    }
    public function hasLowerLevelThan(UserRole $role): bool
    {
        return $this->level() < $role->level();
    }
    public function hasSameLevelAs(UserRole $role): bool
    {
        return $this->level() === $role->level();
    }
    public function canManageRole(UserRole $role): bool
    {
        return $this->hasHigherLevelThan($role);
    }
    public static function options(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
    }
    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn($case) => [$case->value => ['label' => $case->label(), 'description' => $case->description(), 'icon' => $case->icon(), 'color' => $case->color(), 'level' => $case->level(), 'priority' => $case->priority(), 'is_admin' => $case->isAdmin(), 'is_staff' => $case->isStaff(), 'is_customer' => $case->isCustomer(), 'can_access_admin' => $case->canAccessAdmin(), 'can_manage_users' => $case->canManageUsers(), 'can_manage_products' => $case->canManageProducts(), 'can_manage_orders' => $case->canManageOrders(), 'can_manage_settings' => $case->canManageSettings(), 'can_view_analytics' => $case->canViewAnalytics(), 'can_manage_content' => $case->canManageContent(), 'can_manage_inventory' => $case->canManageInventory(), 'can_manage_marketing' => $case->canManageMarketing(), 'can_view_reports' => $case->canViewReports(), 'can_manage_system' => $case->canManageSystem(), 'permissions' => $case->permissions()]])->toArray();
    }
    public static function admin(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isAdmin());
    }
    public static function staff(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isStaff());
    }
    public static function customers(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isCustomer());
    }
    public static function withAdminAccess(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->canAccessAdmin());
    }
    public static function ordered(): Collection
    {
        return collect(self::cases())->sortBy('priority');
    }
    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())->first(fn($case) => $case->label() === $label);
    }
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public static function labels(): array
    {
        return collect(self::cases())->map(fn($case) => $case->label())->toArray();
    }
    public function toArray(): array
    {
        return ['value' => $this->value, 'label' => $this->label(), 'description' => $this->description(), 'icon' => $this->icon(), 'color' => $this->color(), 'level' => $this->level(), 'priority' => $this->priority(), 'is_admin' => $this->isAdmin(), 'is_staff' => $this->isStaff(), 'is_customer' => $this->isCustomer(), 'can_access_admin' => $this->canAccessAdmin(), 'can_manage_users' => $this->canManageUsers(), 'can_manage_products' => $this->canManageProducts(), 'can_manage_orders' => $this->canManageOrders(), 'can_manage_settings' => $this->canManageSettings(), 'can_view_analytics' => $this->canViewAnalytics(), 'can_manage_content' => $this->canManageContent(), 'can_manage_inventory' => $this->canManageInventory(), 'can_manage_marketing' => $this->canManageMarketing(), 'can_view_reports' => $this->canViewReports(), 'can_manage_system' => $this->canManageSystem(), 'permissions' => $this->permissions()];
    }
}