<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Users\AssignRoleAction;
use App\Actions\Users\CreateUserAction;
use App\Actions\Users\UpdateUserProfileAction;
use App\Data\Common\ServiceResponseData;
use App\Data\Users\CreateUserData;
use App\Data\Users\UpdateUserProfileData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * User management service handling user lifecycle and profile operations
 *
 * Responsibilities:
 * - User registration and profile management
 * - Role and permission assignment
 * - Account activation and deactivation
 * - User preferences and settings
 */
final class UserManagementService extends BaseService
{
    public function __construct(
        private readonly CreateUserAction $createUserAction,
        private readonly UpdateUserProfileAction $updateProfileAction,
        private readonly AssignRoleAction $assignRoleAction,
        private readonly NotificationService $notificationService
    ) {
        parent::__construct();
    }

    /**
     * Create new user account
     */
    public function createUser(CreateUserData $userData): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($userData) {
            $this->withContext([
                'operation' => 'create_user',
                'email'     => $userData->email,
            ]);

            // Check if user already exists
            if (User::where('email', $userData->email)->exists()) {
                return $this->error(__('users.email_already_exists'));
            }

            // Create user
            $user = $this->createUserAction->execute($userData);

            // Assign default role
            if ($userData->role) {
                $this->assignRoleAction->execute($user, $userData->role);
            }

            // Send welcome notification
            $this->notificationService->createNotification(
                $user,
                'App\\Notifications\\WelcomeNotification',
                ['name' => $user->name]
            );

            $this->log('info', 'User created successfully', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => $userData->role,
            ]);

            return $user;
        });
    }

    /**
     * Update user profile information
     */
    public function updateProfile(User $user, UpdateUserProfileData $profileData): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($user, $profileData) {
            $this->withContext([
                'operation' => 'update_profile',
                'user_id'   => $user->id,
            ]);

            // Validate ownership (users can only update their own profile unless admin)
            if (! $this->canUpdateProfile($user)) {
                return $this->error(__('users.access_denied'));
            }

            // Check email uniqueness if changed
            if ($profileData->email !== $user->email) {
                if (User::where('email', $profileData->email)->where('id', '!=', $user->id)->exists()) {
                    return $this->error(__('users.email_already_exists'));
                }
            }

            // Update profile
            $updatedUser = $this->updateProfileAction->execute($user, $profileData);

            $this->log('info', 'User profile updated', [
                'user_id'        => $user->id,
                'updated_fields' => array_keys($profileData->toArray()),
            ]);

            return $updatedUser;
        });
    }

    /**
     * Assign role to user
     */
    public function assignRole(User $user, string $roleName): ServiceResponseData
    {
        return $this->execute(function () use ($user, $roleName) {
            $this->withContext([
                'operation' => 'assign_role',
                'user_id'   => $user->id,
                'role'      => $roleName,
            ]);

            // Validate admin permissions
            if (! $this->user?->hasRole('admin')) {
                return $this->error(__('users.insufficient_permissions'));
            }

            // Check if role exists
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                return $this->error(__('users.role_not_found'));
            }

            // Assign role
            $this->assignRoleAction->execute($user, $roleName);

            $this->log('info', 'Role assigned to user', [
                'user_id'     => $user->id,
                'role'        => $roleName,
                'assigned_by' => $this->user?->id,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(User $user, string $reason): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($user, $reason) {
            $this->withContext([
                'operation' => 'deactivate_user',
                'user_id'   => $user->id,
                'reason'    => $reason,
            ]);

            // Validate admin permissions
            if (! $this->user?->hasRole('admin')) {
                return $this->error(__('users.insufficient_permissions'));
            }

            // Prevent self-deactivation
            if ($user->id === $this->user?->id) {
                return $this->error(__('users.cannot_deactivate_self'));
            }

            // Deactivate user
            $user->update([
                'is_active'           => false,
                'deactivated_at'      => now(),
                'deactivation_reason' => $reason,
            ]);

            // Revoke all sessions
            $user->tokens()->delete();

            $this->log('warning', 'User account deactivated', [
                'user_id'        => $user->id,
                'reason'         => $reason,
                'deactivated_by' => $this->user?->id,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Activate user account
     */
    public function activateUser(User $user): ServiceResponseData
    {
        return $this->execute(function () use ($user) {
            $this->withContext([
                'operation' => 'activate_user',
                'user_id'   => $user->id,
            ]);

            // Validate admin permissions
            if (! $this->user?->hasRole('admin')) {
                return $this->error(__('users.insufficient_permissions'));
            }

            // Activate user
            $user->update([
                'is_active'           => true,
                'deactivated_at'      => null,
                'deactivation_reason' => null,
            ]);

            $this->log('info', 'User account activated', [
                'user_id'      => $user->id,
                'activated_by' => $this->user?->id,
            ]);

            return $user->fresh();
        });
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): ServiceResponseData
    {
        return $this->execute(function () use ($user, $currentPassword, $newPassword) {
            $this->withContext([
                'operation' => 'change_password',
                'user_id'   => $user->id,
            ]);

            // Validate ownership
            if ($user->id !== $this->user?->id && ! $this->user?->hasRole('admin')) {
                return $this->error(__('users.access_denied'));
            }

            // Verify current password (skip for admin)
            if ($user->id === $this->user?->id && ! Hash::check($currentPassword, $user->password)) {
                return $this->error(__('users.current_password_incorrect'));
            }

            // Update password
            $user->update([
                'password'            => Hash::make($newPassword),
                'password_changed_at' => now(),
            ]);

            // Revoke all other sessions
            $user->tokens()->delete();

            $this->log('info', 'User password changed', [
                'user_id'    => $user->id,
                'changed_by' => $this->user?->id,
            ]);

            return $this->success(null, __('users.password_changed_successfully'));
        });
    }

    /**
     * Get user statistics for admin dashboard
     */
    public function getUserStatistics(): ServiceResponseData
    {
        return $this->execute(function () {
            // Validate admin permissions
            if (! $this->user?->hasRole('admin')) {
                return $this->error(__('users.insufficient_permissions'));
            }

            $stats = [
                'total_users'                 => User::count(),
                'active_users'                => User::where('is_active', true)->count(),
                'inactive_users'              => User::where('is_active', false)->count(),
                'users_registered_today'      => User::whereDate('created_at', today())->count(),
                'users_registered_this_week'  => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'users_registered_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ];

            return $stats;
        });
    }

    /**
     * Check if current user can update the given user's profile
     */
    private function canUpdateProfile(User $targetUser): bool
    {
        // Users can update their own profile
        if ($this->user?->id === $targetUser->id) {
            return true;
        }

        // Admins can update any profile
        if ($this->user?->hasRole('admin')) {
            return true;
        }

        return false;
    }
}
