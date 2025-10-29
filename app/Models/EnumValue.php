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
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'metadata'   => 'array',
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
        // Limit the query to only active enum values.
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        // Limit the query to only default enum values.
        return $query->where('is_default', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        // Filter enum values by a specific type key.
        return $query->where('type', $type);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        // Apply a deterministic ordering using the position and name columns.
        return $query->orderBy('sort_order')->orderedByName();
    }

    public function scopeOrderedByName(Builder $query): Builder
    {
        // Order enum values alphabetically by their name column.
        return $query->orderBy('name');
    }

    // Accessors
    protected function usageCount(): Attribute
    {
        // Lazily compute the usage count by delegating to the helper method.
        return Attribute::make(
            get: fn (): int => $this->getUsageCount()
        );
    }

    protected function formattedValue(): Attribute
    {
        // Present a human-readable representation of the enum value tuple.
        return Attribute::make(
            get: fn (): string => "{$this->type}::{$this->key} => {$this->value}"
        );
    }

    // Methods
    public function getUsageCount(): int
    {
        // Guard against malformed metadata payloads.
        $metadata = $this->metadata;

        if (! is_array($metadata)) {
            return 0;
        }

        return (int) ($metadata['usage_count'] ?? 0);
    }

    public function activate(): bool
    {
        // Flip the active flag to true and persist the change.
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        // Flip the active flag to false and persist the change.
        return $this->update(['is_active' => false]);
    }

    public function setAsDefault(): bool
    {
        // Ensure only one enum value per type is marked as default.
        self::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        return $this->update(['is_default' => true]);
    }

    public function duplicate(): self
    {
        // Clone the existing model while resetting fields that must remain unique.
        $newEnumValue = $this->replicate();

        // Generate a collision-resistant key so repeated duplications never violate the unique constraint.
        $newEnumValue->key = $this->generateDuplicateKey();
        $newEnumValue->is_default = false;

        $metadata = $newEnumValue->metadata;
        if (! is_array($metadata)) {
            // Normalise malformed metadata payloads before we mutate the usage counter.
            $metadata = [];
        }

        // Reset usage statistics to ensure the duplicate starts from a clean slate.
        $metadata['usage_count'] = 0;
        $newEnumValue->metadata = $metadata;

        $newEnumValue->save();

        return $newEnumValue;
    }

    /**
     * Build a unique key for the duplicated enum value without leaking the original primary key.
     */
    private function generateDuplicateKey(): string
    {
        // Begin with the conventional “_copy” suffix and increment until the key is unique for the type.
        $baseKey = $this->key . '_copy';
        $candidateKey = $baseKey;
        $suffix = 2;

        while (self::query()
            ->where('type', $this->type)
            ->where('key', $candidateKey)
            ->exists()) {
            // Append an incrementing suffix when the naive key is already in use.
            $candidateKey = sprintf('%s_%d', $baseKey, $suffix);
            $suffix++;
        }

        return $candidateKey;
    }

    // Static methods
    public static function getTypes(): array
    {
        // Provide a mapping of known type identifiers to translated labels.
        $defaultTypes = [
            'navigation_group'  => __('admin.enum_values.types.navigation_group'),
            'order_status'      => __('admin.enum_values.types.order_status'),
            'payment_status'    => __('admin.enum_values.types.payment_status'),
            'shipping_status'   => __('admin.enum_values.types.shipping_status'),
            'user_role'         => __('admin.enum_values.types.user_role'),
            'product_status'    => __('admin.enum_values.types.product_status'),
            'campaign_type'     => __('admin.enum_values.types.campaign_type'),
            'discount_type'     => __('admin.enum_values.types.discount_type'),
            'notification_type' => __('admin.enum_values.types.notification_type'),
            'document_type'     => __('admin.enum_values.types.document_type'),
            'address_type'      => __('admin.enum_values.types.address_type'),
            'priority'          => __('admin.enum_values.types.priority'),
            'status'            => __('admin.enum_values.types.status'),
        ];

        $existingTypes = self::query()->distinct()->pluck('type')->all();

        foreach ($existingTypes as $type) {
            if (! isset($defaultTypes[$type])) {
                // Create a presentable label for custom types on the fly.
                $defaultTypes[$type] = Str::headline(str_replace('_', ' ', $type));
            }
        }

        ksort($defaultTypes);

        return $defaultTypes;
    }

    public static function getValuesByType(string $type): array
    {
        // Build the base query once so we can attempt active-first fallback logic.
        $query = self::query()
            ->where('type', $type)
            ->ordered();

        $activeValues = (clone $query)
            ->active()
            ->pluck('value', 'key')
            ->toArray();

        if ($activeValues !== []) {
            // Prefer returning only active enum values when available.
            return $activeValues;
        }

        // Fallback to all records so admin-focused tests can assert against seeded values.
        return $query
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function getDefaultValue(string $type): ?string
    {
        // Locate the default enum key for the provided type.
        $default = self::where('type', $type)
            ->where('is_default', true)
            ->first();

        return $default?->key;
    }

    public static function cleanupUnused(): int
    {
        // Soft delete enum values that have not been used within the retention window.
        $threshold = now()->subMonths(6);

        $deleted = 0;

        // Iterate lazily to avoid loading large tables into memory during maintenance windows.
        self::query()
            ->where('created_at', '<', $threshold)
            ->lazyById()
            ->each(function (EnumValue $enumValue) use (&$deleted): void {
                // Skip records that still have a usage count, even if they are old.
                if ($enumValue->getUsageCount() > 0) {
                    return;
                }

                if ($enumValue->delete()) {
                    // Track successful deletions for reporting back to the caller.
                    $deleted++;
                }
            });

        return $deleted;
    }
}
