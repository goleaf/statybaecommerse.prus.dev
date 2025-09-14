<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class, 'company', 'name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByIndustry($query, string $industry)
    {
        return $query->where('industry', $industry);
    }

    public function scopeBySize($query, string $size)
    {
        return $query->where('size', $size);
    }

    // Accessors
    public function getSubscriberCountAttribute(): int
    {
        return $this->subscribers()->count();
    }

    public function getActiveSubscriberCountAttribute(): int
    {
        return $this->subscribers()->active()->count();
    }
}
