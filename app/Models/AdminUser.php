<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * AdminUser
 *
 * Eloquent model representing the AdminUser entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $hidden
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminUser query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class AdminUser extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * Guard name for Spatie permissions (separate admin guard).
     */
    protected string $guard_name = 'admin';

    /**
     * Allow mass-assignment for the primary profile fields as well as
     * verification metadata so table actions can toggle verification state.
     */
    protected $fillable = ['name', 'email', 'password', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Always normalize the casing before sorting so administrators appear deterministically.
        return $query->orderByRaw('LOWER(name) ASC, name ASC');
    }

    /**
     * Handle canAccessPanel functionality with proper error handling.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return AuthorizationMatrix::check('panel', 'access', $this);
    }
}
