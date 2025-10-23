<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        $metadata = $this->metadata;

        if (! is_array($metadata)) {
            return 0;
        }

        return (int) ($metadata['usage_count'] ?? 0);
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

        $metadata = $newEnumValue->metadata;
        if (! is_array($metadata)) {
            $metadata = [];
        }

        $metadata['usage_count'] = 0;
        $newEnumValue->metadata = $metadata;

        $newEnumValue->save();

        return $newEnumValue;
    }

    // Static methods
    public static function getTypes(): array
    {
        $defaultTypes = [
            'navigation_group' => __('admin.enum_values.types.navigation_group'),
            'order_status' => __('admin.enum_values.types.order_status'),
            'payment_status' => __('admin.enum_values.types.payment_status'),
            'shipping_status' => __('admin.enum_values.types.shipping_status'),
            'user_role' => __('admin.enum_values.types.user_role'),
            'product_status' => __('admin.enum_values.types.product_status'),
            'campaign_type' => __('admin.enum_values.types.campaign_type'),
            'discount_type' => __('admin.enum_values.types.discount_type'),
            'notification_type' => __('admin.enum_values.types.notification_type'),
            'document_type' => __('admin.enum_values.types.document_type'),
            'address_type' => __('admin.enum_values.types.address_type'),
            'priority' => __('admin.enum_values.types.priority'),
            'status' => __('admin.enum_values.types.status'),
        ];

        $existingTypes = self::query()->distinct()->pluck('type')->all();

        foreach ($existingTypes as $type) {
            if (! isset($defaultTypes[$type])) {
                $defaultTypes[$type] = Str::headline(str_replace('_', ' ', $type));
            }
        }

        ksort($defaultTypes);

        return $defaultTypes;
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
        $threshold = now()->subMonths(6);

        $candidates = self::query()
            ->where('created_at', '<', $threshold)
            ->get();

        $deleted = 0;

        foreach ($candidates as $enumValue) {
            if ($enumValue->getUsageCount() > 0) {
                continue;
            }

            if ($enumValue->delete()) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
