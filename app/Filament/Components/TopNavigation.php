<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Enums\NavigationGroup;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Facades\Auth;

/**
 * Render the top navigation bar shared across the Filament dashboard.
 */
class TopNavigation extends Widget
{
    protected string $view = 'filament.components.top-navigation';

    /**
     * Match the parent widget signature while forcing a full-width layout by default.
     */
    protected int|string|array|null $columnSpan = 'full';

    protected static ?int $sort = -100;

    /**
     * Cache the resolved Filament guard user for the lifetime of the widget render.
     */
    private ?AuthenticatableContract $resolvedUser = null;

    /**
     * Track whether we have attempted to resolve the authenticated user to avoid redundant lookups.
     */
    private bool $userResolved = false;

    public function getViewData(): array
    {
        $user = $this->resolveUser();

        return [
            'navigationGroups' => $this->getNavigationGroups($user),
            'user'             => $user,
            'isAdmin'          => $user?->is_admin ?? false,
        ];
    }

    /**
     * Build the navigation payload while filtering groups by the viewer's permissions.
     *
     * @return array<int, array{key: string, label: string, description: string, icon: string, color: string, priority: int, is_core: bool, is_admin_only: bool, is_public: bool, requires_permission: bool, permission: string}>
     */
    protected function getNavigationGroups(?AuthenticatableContract $user): array
    {
        $groups = [];

        foreach (NavigationGroup::ordered() as $group) {
            // Only include groups that the current user is allowed to see.
            if (! $this->canAccessGroup($group, $user)) {
                continue;
            }

            $groups[] = [
                'key'         => $group->value,
                'label'       => $group->label(),
                'description' => $group->description(),
                'icon'        => $group->icon(),
                'color'       => $group->color(),
                'priority'    => $group->priority(),
                'is_core'     => $group->isCore(),
                // Expose whether the section is admin-only regardless of the current viewer.
                'is_admin_only'       => $group->isAdminOnly(),
                'is_public'           => $group->isPublic(),
                'requires_permission' => $group->requiresPermission(),
                'permission'          => $group->getPermission(),
            ];
        }

        return $groups;
    }

    /**
     * Determine whether the supplied navigation group should be visible to the current viewer.
     */
    protected function canAccessGroup(NavigationGroup $group, ?AuthenticatableContract $user): bool
    {
        if (! $user) {
            return false;
        }

        // Administrators (flag or role) should always see the full navigation tree.
        $hasAdminFlag = (bool) data_get($user, 'is_admin', false);
        $hasAdminRole = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'Admin']);

        if ($hasAdminFlag || $hasAdminRole) {
            return true;
        }

        // Enforce permission-gated groups for non-admin users.
        if ($group->requiresPermission()) {
            if ($user instanceof AuthorizableContract) {
                return $user->can($group->getPermission());
            }

            return false;
        }

        // Prevent non-admin users from seeing admin-only groups.
        if ($group->isAdminOnly()) {
            return false;
        }

        return true;
    }

    /**
     * Resolve and memoize the authenticated user once per widget lifecycle.
     */
    private function resolveUser(): ?AuthenticatableContract
    {
        if (! $this->userResolved) {
            $this->resolvedUser = Auth::user();
            $this->userResolved = true;
        }

        return $this->resolvedUser;
    }
}
