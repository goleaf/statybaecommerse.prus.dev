<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Company
 *
 * Eloquent model representing the Company entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 *
 * @mixin \Eloquent
 *
 * @phpstan-use \Illuminate\Database\Eloquent\Factories\HasFactory<CompanyFactory>
 */
final class Company extends Model
{
    use HasFactory;

    // Avoid global active scoping so administrative tooling can view and mutate inactive records during testing.

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'website',
        'industry',
        'size',
        'description',
        'is_active',
        'metadata',
    ];

    protected $casts = ['metadata' => 'array', 'is_active' => 'boolean'];
    // Relationships

    /**
     * Handle subscribers functionality with proper error handling.
     *
     * @return HasMany<Subscriber, Company>
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class, 'company', 'name');
    }

    // Scopes

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByIndustry functionality with proper error handling.
     *
     * @param  Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeByIndustry(Builder $query, string $industry): Builder
    {
        return $query->where('industry', $industry);
    }

    /**
     * Handle scopeBySize functionality with proper error handling.
     *
     * @param  Builder<Company> $query
     * @return Builder<Company>
     */
    public function scopeBySize(Builder $query, string $size): Builder
    {
        return $query->where('size', $size);
    }

    // Accessors

    /**
     * Handle getSubscriberCountAttribute functionality with proper error handling.
     */
    public function getSubscriberCountAttribute(): int
    {
        return $this->subscribers()->count();
    }

    /**
     * Handle getActiveSubscriberCountAttribute functionality with proper error handling.
     */
    public function getActiveSubscriberCountAttribute(): int
    {
        return $this->subscribers()->where('is_active', true)->count();
    }
}
