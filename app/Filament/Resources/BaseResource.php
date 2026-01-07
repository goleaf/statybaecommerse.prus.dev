<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Authorization\AuthorizationMatrix;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;

/**
 * Base resource class that provides consistent authorization patterns
 * for all admin resources in the Filament admin panel.
 */
abstract class BaseResource extends Resource
{
    /**
     * Get the authorization resource name for this resource.
     * Override this method in child classes to specify the correct resource name.
     */
    protected static function getAuthorizationResource(): string
    {
        // Default to the model name in snake_case
        $modelClass = static::getModel();
        $modelName = class_basename($modelClass);
        
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $modelName));
    }

    /**
     * Determine if the current user can view any records.
     */
    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'viewAny');
    }

    /**
     * Determine if the current user can view the specified record.
     */
    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'view');
    }

    /**
     * Determine if the current user can create records.
     */
    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'create');
    }

    /**
     * Determine if the current user can edit the specified record.
     */
    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'update');
    }

    /**
     * Determine if the current user can delete the specified record.
     */
    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'delete');
    }

    /**
     * Determine if the current user can delete any records.
     */
    public static function canDeleteAny(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'delete');
    }

    /**
     * Determine if the current user can restore the specified record.
     */
    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'restore');
    }

    /**
     * Determine if the current user can restore any records.
     */
    public static function canRestoreAny(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'restore');
    }

    /**
     * Determine if the current user can force delete the specified record.
     */
    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'forceDelete');
    }

    /**
     * Determine if the current user can force delete any records.
     */
    public static function canForceDeleteAny(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'forceDelete');
    }

    /**
     * Determine if the current user can replicate the specified record.
     */
    public static function canReplicate(Model $record): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'replicate');
    }

    /**
     * Determine if the current user can reorder records.
     */
    public static function canReorder(): bool
    {
        return AuthorizationMatrix::check(static::getAuthorizationResource(), 'reorder');
    }
}