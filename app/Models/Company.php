<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
 */
#[ScopedBy([ActiveScope::class])]
final class Company extends Model
{
    use HasFactory;

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
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class, 'company', 'name');
    }

    // Scopes

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByIndustry functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Handle scopeBySize functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeBySize($query, string $size)
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
        return $this->subscribers()->active()->count();
    }
}
