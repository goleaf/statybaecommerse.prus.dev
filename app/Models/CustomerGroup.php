<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

final class CustomerGroup extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'customer_groups';

    public array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'discount_percentage',
        'is_enabled',
        'is_active',
        'conditions',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'is_enabled' => 'boolean',
            'conditions' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')
            ->withTimestamps();
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_customer_groups');
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'group_price_list', 'group_id', 'price_list_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where('discount_percentage', '>', 0);
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function hasDiscountRate(): bool
    {
        return (float) $this->discount_percentage > 0;
    }

    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->is_enabled;
    }

    public function setIsActiveAttribute(bool $value): void
    {
        $this->attributes['is_enabled'] = $value;
    }
}
