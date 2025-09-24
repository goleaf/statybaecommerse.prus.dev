<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * UserDataCreator
 *
 * View Creator that provides user-specific data to views.
 * This includes authentication status, user information, and permissions.
 */
final class UserDataCreator
{
    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $user = Auth::user();

        $view->with([
            'isAuthenticated' => Auth::check(),
            'user' => $user,
            'userRole' => $user?->getRoleNames()->first(),
            'userPermissions' => $user?->getAllPermissions()->pluck('name')->toArray() ?? [],
            'isAdmin' => $user?->hasRole('admin') ?? false,
            'isCustomer' => $user?->hasRole('customer') ?? false,
            'userNotifications' => $this->getUserNotifications($user),
            'userPreferences' => $this->getUserPreferences($user),
        ]);
    }

    /**
     * Get user notifications count.
     */
    private function getUserNotifications(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return $user->notifications()
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get user preferences.
     */
    private function getUserPreferences(?User $user): array
    {
        if (! $user) {
            return [
                'locale' => app()->getLocale(),
                'currency' => current_currency(),
                'theme' => 'light',
                'notifications' => [
                    'email' => false,
                    'push' => false,
                    'sms' => false,
                ],
            ];
        }

        return [
            'locale' => $user->preferred_locale ?? app()->getLocale(),
            'currency' => $user->preferred_currency ?? current_currency(),
            'theme' => $user->theme_preference ?? 'light',
            'notifications' => [
                'email' => $user->email_notifications ?? true,
                'push' => $user->push_notifications ?? false,
                'sms' => $user->sms_notifications ?? false,
            ],
        ];
    }
}
