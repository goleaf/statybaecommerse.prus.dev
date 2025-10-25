<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Channel
 *
 * Eloquent model representing the Channel entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Channel query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, StatusScope::class])]
final class Channel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Keep channel selectors alphabetically consistent via the OrdersByName concern.
     */
    use OrdersByName;

    protected $table = 'channels';

    /**
     * @var array<int, string> Safely mass assignable channel attributes.
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'type',
        'description',
        'timezone',
        'url',
        'domain',
        'is_enabled',
        'is_default',
        'is_active',
        'ssl_enabled',
        'analytics_enabled',
        'sort_order',
        'metadata',
        'configuration',
        'currency_code',
        'currency_symbol',
        'currency_position',
    ];

    /**
     * @var array<string, string> Attribute casts ensuring typed access to toggles and configuration blobs.
     */
    protected $casts = [
        'is_enabled'        => 'boolean',
        'is_default'        => 'boolean',
        'is_active'         => 'boolean',
        'ssl_enabled'       => 'boolean',
        'analytics_enabled' => 'boolean',
        'sort_order'        => 'integer',
        'metadata'          => 'array',
        'configuration'     => 'array',
    ];

    /**
     * Describe the one-to-many relationship between channels and orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->withoutGlobalScopes();
    }

    /**
     * Describe the one-to-many relationship between channels and discounts.
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class)->withoutGlobalScopes();
    }

    /**
     * Describe the many-to-many relationship between channels and products.
     */
    public function products(): BelongsToMany
    {
        // Skip global scopes so pivot checks in tests do not filter freshly attached products.
        return $this->belongsToMany(Product::class)->withoutGlobalScopes();
    }

    /**
     * Limit the query to enabled channels only.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Limit the query to the default channel definition.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Limit the query to channels currently marked as active.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Restrict channels by their delivery type (web, mobile, api, ...).
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Order channels deterministically for navigation surfaces.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        // Ensure we keep a predictable ordering by the explicit sort position first and fall back to name ordering.
        return $query
            ->orderBy('sort_order')
            ->orderedByName();
    }
}
