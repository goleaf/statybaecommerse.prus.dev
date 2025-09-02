<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sh_customer_groups';

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_rate',
        'is_enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'discount_rate' => 'decimal:4',
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sh_customer_group_user', 'group_id', 'user_id')
            ->withTimestamps();
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'sh_discount_customer_groups');
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'sh_group_price_list', 'group_id', 'price_list_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where('discount_rate', '>', 0);
    }

    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    public function hasDiscountRate(): bool
    {
        return $this->discount_rate > 0;
    }
}




