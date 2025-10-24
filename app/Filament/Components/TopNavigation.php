<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Enums\NavigationGroup;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TopNavigation extends Widget
{
    protected string $view = 'filament.components.top-navigation';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100;

    public function getViewData(): array
    {
        $user = Auth::user();

        return [
            'navigationGroups' => $this->getNavigationGroups(),
            'user'             => $user,
            'isAdmin'          => $user?->is_admin ?? false,
        ];
    }

    protected function getNavigationGroups(): array
    {
        $groups = [];

        foreach (NavigationGroup::ordered() as $group) {
            // Only include groups that the current user is allowed to see.
            if (! $this->canAccessGroup($group)) {
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

    protected function canAccessGroup(NavigationGroup $group): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        // Administrators (flag or role) should always see the full navigation tree.
        if ($user->is_admin || $user->hasAnyRole(['admin', 'Admin'])) {
            return true;
        }

        // Enforce permission-gated groups for non-admin users.
        if ($group->requiresPermission()) {
            return $user->can($group->getPermission());
        }

        // Prevent non-admin users from seeing admin-only groups.
        if ($group->isAdminOnly()) {
            return false;
        }

        return true;
    }
}
