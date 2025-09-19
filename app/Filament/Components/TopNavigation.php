<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Enums\NavigationGroup;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TopNavigation extends Widget
{
    protected static string $view = 'filament.components.top-navigation';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -100;

    public function getViewData(): array
    {
        $user = Auth::user();
        
        return [
            'navigationGroups' => $this->getNavigationGroups(),
            'user' => $user,
            'isAdmin' => $user?->is_admin ?? false,
        ];
    }

    protected function getNavigationGroups(): array
    {
        $groups = [];
        
        foreach (NavigationGroup::ordered() as $group) {
            if ($this->canAccessGroup($group)) {
                $groups[] = [
                    'key' => $group->value,
                    'label' => $group->label(),
                    'description' => $group->description(),
                    'icon' => $group->icon(),
                    'color' => $group->color(),
                    'priority' => $group->priority(),
                    'is_core' => $group->isCore(),
                    'is_admin_only' => $group->isAdminOnly(),
                    'is_public' => $group->isPublic(),
                    'requires_permission' => $group->requiresPermission(),
                    'permission' => $group->getPermission(),
                ];
            }
        }
        
        return $groups;
    }

    protected function canAccessGroup(NavigationGroup $group): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Check if group requires specific permissions
        if ($group->requiresPermission()) {
            return $user->can($group->getPermission());
        }
        
        // Admin-only groups
        if ($group->isAdminOnly()) {
            return $user->is_admin || $user->hasAnyRole(['admin', 'Admin']);
        }
        
        return true;
    }
}
