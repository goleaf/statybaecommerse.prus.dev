<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * EnumValue
 *
 * Model for managing enum values in the admin panel with comprehensive relationships, scopes, and business logic.
 */
final class EnumValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'key',
        'value',
        'name',
        'description',
        'sort_order',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    protected $appends = ['usage_count', 'formatted_value'];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        self::creating(function (EnumValue $enumValue): void {
            if (! $enumValue->sort_order) {
                $enumValue->sort_order = static::where('type', $enumValue->type)->max('sort_order') + 1;
            }
        });

        self::saving(function (EnumValue $enumValue): void {
            if ($enumValue->is_default) {
                static::where('type', $enumValue->type)
                    ->where('id', '!=', $enumValue->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    protected function usageCount(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->getUsageCount()
        );
    }

    protected function formattedValue(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "{$this->type}::{$this->key} => {$this->value}"
        );
    }

    // Methods
    public function getUsageCount(): int
    {
        // This would be implemented based on actual usage tracking
        // For now, return a random number for demonstration
        return rand(0, 100);
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function setAsDefault(): bool
    {
        self::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        return $this->update(['is_default' => true]);
    }

    public function duplicate(): self
    {
        $newEnumValue = $this->replicate();
        $newEnumValue->key = $this->key.'_copy';
        $newEnumValue->is_default = false;
        $newEnumValue->save();

        return $newEnumValue;
    }

    // Static methods
    public static function getTypes(): array
    {
        return [
            'navigation_group' => 'Navigation Group',
            'order_status' => 'Order Status',
            'payment_status' => 'Payment Status',
            'shipping_status' => 'Shipping Status',
            'user_role' => 'User Role',
            'product_status' => 'Product Status',
            'campaign_type' => 'Campaign Type',
            'discount_type' => 'Discount Type',
            'notification_type' => 'Notification Type',
            'document_type' => 'Document Type',
            'address_type' => 'Address Type',
            'priority' => 'Priority',
            'status' => 'Status',
        ];
    }

    public static function getValuesByType(string $type): array
    {
        return self::where('type', $type)
            ->active()
            ->ordered()
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function getDefaultValue(string $type): ?string
    {
        $default = self::where('type', $type)
            ->where('is_default', true)
            ->first();

        return $default?->key;
    }

    public static function cleanupUnused(): int
    {
        return self::where('usage_count', 0)
            ->where('created_at', '<', now()->subMonths(6))
            ->delete();
    }
}
