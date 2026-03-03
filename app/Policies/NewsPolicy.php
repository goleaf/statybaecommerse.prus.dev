<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\News;
use App\Models\User;

final class NewsPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user);
    }

    public function view(User|AdminUser $user, News $news): bool
    {
        return $this->hasPermission($user);
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user);
    }

    public function update(User|AdminUser $user, News $news): bool
    {
        return $this->hasPermission($user);
    }

    public function delete(User|AdminUser $user, News $news): bool
    {
        return $this->hasPermission($user);
    }

    public function restore(User|AdminUser $user, News $news): bool
    {
        return $this->hasPermission($user);
    }

    public function forceDelete(User|AdminUser $user, News $news): bool
    {
        return $this->hasPermission($user);
    }

    private function hasPermission(User|AdminUser $user): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        if (method_exists($user, 'getAttribute') && (bool) $user->getAttribute('is_admin')) {
            return true;
        }

        if (isset($user->is_admin) && (bool) $user->is_admin) {
            return true;
        }

        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can('manage_news');
    }
}
