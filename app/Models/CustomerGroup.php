<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomerGroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * CustomerGroup
 *
 * Eloquent model representing the CustomerGroup entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed                     $table
 * @property array<int, string>        $translatable
 * @property mixed                     $fillable
 * @property array<string, mixed>|null $metadata
 *
 * @method static Builder|CustomerGroup newModelQuery()
 * @method static Builder|CustomerGroup newQuery()
 * @method static Builder|CustomerGroup query()
 *
 * @mixin \Eloquent
 */
final class CustomerGroup extends Model
{
    /** @use HasFactory<CustomerGroupFactory> */
    use HasFactory;

    use HasTranslations {
        getTranslations as getTranslationsFromTrait;
    }
    use SoftDeletes;

    protected $table = 'customer_groups';

    /**
     * @var array<int, string>
     */
    public array $translatable = ['name', 'description'];

    protected static function booted(): void
    {
        // Generate a slug automatically so legacy factories and direct model usage can
        // create customer groups without explicitly specifying one.
        self::creating(function (CustomerGroup $group): void {
            if (! $group->slug) {
                /** @var array<string, string>|string|null $rawName */
                $rawName = $group->getAttribute('name');

                $resolvedName = is_array($rawName)
                    ? (string) (Arr::first($rawName) ?? '')
                    : (string) ($rawName ?? '');

                if ($resolvedName === '') {
                    $code = $group->getAttribute('code');

                    if (is_string($code) && $code !== '') {
                        $resolvedName = $code;
                    }
                }

                $group->slug = Str::slug($resolvedName) ?: Str::random(8);
            }
        });

        self::saving(function (CustomerGroup $group): void {
            foreach (['name', 'description'] as $attribute) {
                $raw = $group->getAttributes()[$attribute] ?? null;

                if (! is_string($raw)) {
                    continue;
                }

                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) === 1) {
                    $collapsedValue = Arr::first($decoded, default: '');
                    $group->attributes[$attribute] = is_scalar($collapsedValue) ? (string) $collapsedValue : '';
                }
            }
        });
    }

    /**
     * Whitelist both the legacy segmentation flags and the modern management fields.
     *
     * Including the entire set keeps mass-assignment in sync with the Filament
     * resource tests that submit the form using attributes such as `is_active`
     * and `has_special_pricing`.
     */
    protected $fillable = [
        'name',
        'code',
        'color',
        'icon',
        'description',
        'slug',
        'discount_percentage',
        'discount_fixed',
        'has_special_pricing',
        'has_volume_discounts',
        'can_view_prices',
        'can_place_orders',
        'can_view_catalog',
        'can_use_coupons',
        'is_enabled',
        'is_active',
        'is_default',
        'sort_order',
        'type',
        'metadata',
        'conditions',
    ];

    /**
     * Bootstrap the model and ensure the slug column is automatically maintained.
     */
    protected static function boot(): void
    {
        parent::boot();

        self::creating(static function (CustomerGroup $customerGroup): void {
            // Derive the slug from the code or name so the database constraint is always satisfied.
            $slug = $customerGroup->getAttribute('slug');
            $customerGroup->setAttribute('slug', $slug ?? self::generateSlug($customerGroup));
        });

        self::updating(static function (CustomerGroup $customerGroup): void {
            // Refresh the slug when relevant attributes change while respecting manually provided values.
            $slug = $customerGroup->getAttribute('slug');

            if ($customerGroup->isDirty(['name', 'code']) && empty($slug)) {
                $customerGroup->setAttribute('slug', self::generateSlug($customerGroup));
            }
        });
    }

    /**
     * Generate a consistent slug value based on the group code or translated name.
     */
    private static function generateSlug(CustomerGroup $customerGroup): string
    {
        // Prefer the code for deterministic slugs, otherwise fall back to the default locale name.
        $code = $customerGroup->getAttribute('code');
        $translation = $customerGroup->getTranslation('name', app()->getLocale(), false);
        $fallbackName = is_string($translation) ? $translation : '';

        $source = is_string($code) && $code !== ''
            ? $code
            : $fallbackName;

        $resolvedSource = $source !== '' ? $source : Str::random(8);

        return Str::slug($resolvedSource);
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'discount_fixed'       => 'decimal:2',
            'has_special_pricing'  => 'boolean',
            'has_volume_discounts' => 'boolean',
            'can_view_prices'      => 'boolean',
            'can_place_orders'     => 'boolean',
            'can_view_catalog'     => 'boolean',
            'can_use_coupons'      => 'boolean',
            'is_enabled'           => 'boolean',
            'is_active'            => 'boolean',
            'is_default'           => 'boolean',
            'sort_order'           => 'integer',
            'metadata'             => 'array',
            'conditions'           => 'array',
            'deleted_at'           => 'datetime',
        ];
    }

    /**
     * Present the discount percentage as a normalised string for consistency in tests and UI renders.
     *
     * @return Attribute<float|null, float|string|null>
     */
    protected function discountPercentage(): Attribute
    {
        return Attribute::make(
            // Normalise to a float with two decimal precision for modernised integrations.
            get: static function ($value): ?float {
                if ($value === null) {
                    return null;
                }

                if (! is_numeric($value)) {
                    // Bail out gracefully when the persisted value is not numeric to avoid type juggling bugs.
                    return null;
                }

                return round((float) $value, 2);
            },
            set: static function ($value): ?float {
                if ($value === null || $value === '') {
                    return null;
                }

                if (! is_numeric($value)) {
                    return null;
                }

                return round((float) $value, 2);
            },
        );
    }

    /**
     * Handle users functionality with proper error handling.
     */
    /**
     * @return BelongsToMany<User, self>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, CustomerGroup> $relation */
        $relation = $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();

        return $relation;
    }

    /**
     * Handle customers functionality with proper error handling.
     *
     * @return BelongsToMany<User, self>
     */
    public function customers(): BelongsToMany
    {
        /** @var BelongsToMany<User, CustomerGroup> $relation */
        $relation = $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();

        return $relation;
    }

    /**
     * Handle discounts functionality with proper error handling.
     *
     * @return BelongsToMany<Discount, self>
     */
    public function discounts(): BelongsToMany
    {
        /** @var BelongsToMany<Discount, CustomerGroup> $relation */
        $relation = $this->belongsToMany(Discount::class, 'discount_customer_groups');

        return $relation;
    }

    /**
     * Handle priceLists functionality with proper error handling.
     *
     * @return BelongsToMany<PriceList, self>
     */
    public function priceLists(): BelongsToMany
    {
        /** @var BelongsToMany<PriceList, CustomerGroup> $relation */
        $relation = $this->belongsToMany(PriceList::class, 'group_price_list', 'group_id', 'price_list_id');

        return $relation;
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeWithDiscount functionality with proper error handling.
     *
     * @param mixed $query
     */
    /**
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeWithDiscount(Builder $query): Builder
    {
        return $query->where('discount_percentage', '>', 0);
    }

    /**
     * Handle getUsersCountAttribute functionality with proper error handling.
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Handle hasDiscountRate functionality with proper error handling.
     */
    public function hasDiscountRate(): bool
    {
        $rawDiscount = $this->getAttribute('discount_percentage');

        if (! is_numeric($rawDiscount)) {
            // Treat missing or malformed discounts as zero so downstream logic stays predictable.
            return false;
        }

        return (float) $rawDiscount > 0;
    }

    /**
     * Handle getIsActiveAttribute functionality with proper error handling.
     */
    public function getIsActiveAttribute(): bool
    {
        // Prefer the dedicated column when it exists, but gracefully fall back to
        // legacy `is_enabled` values so older factories and seeders keep working.
        $rawActive = $this->attributes['is_active'] ?? null;

        if ($rawActive !== null) {
            return $this->normalizeBoolean($rawActive);
        }

        return $this->normalizeBoolean($this->attributes['is_enabled'] ?? false);
    }

    /**
     * Handle getIsEnabledAttribute functionality with proper error handling.
     */
    public function getIsEnabledAttribute(): bool
    {
        // Provide a mirror fallback so legacy `is_active` data keeps toggles
        // in sync even when only one column was persisted previously.
        $rawEnabled = $this->attributes['is_enabled'] ?? null;

        if ($rawEnabled !== null) {
            return $this->normalizeBoolean($rawEnabled);
        }

        return $this->normalizeBoolean($this->attributes['is_active'] ?? false);
    }

    /**
     * Handle setIsActiveAttribute functionality with proper error handling.
     */
    public function setIsActiveAttribute(mixed $value): void
    {
        // Keep both persistence strategies aligned because different parts of the
        // codebase may still rely on either attribute name.
        $normalized = $this->normalizeBoolean($value);

        $this->attributes['is_active'] = $normalized;
        $this->attributes['is_enabled'] = $normalized;
    }

    /**
     * Handle setIsEnabledAttribute functionality with proper error handling.
     */
    public function setIsEnabledAttribute(mixed $value): void
    {
        // Mirror the active mutator so seeding, factories, and Filament forms can
        // safely write either attribute without drifting column states.
        $normalized = $this->normalizeBoolean($value);

        $this->attributes['is_enabled'] = $normalized;
        $this->attributes['is_active'] = $normalized;
    }

    /**
     * Get metadata field value
     *
     * @param  mixed $default
     * @return mixed
     */
    public function getMetadata(string $key, $default = null)
    {
        $metadata = (array) ($this->metadata ?? []);

        return $metadata[$key] ?? $default;
    }

    /**
     * Set metadata field value
     *
     * @param mixed $value
     */
    public function setMetadata(string $key, $value): void
    {
        $metadata = (array) ($this->metadata ?? []);
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }

    /**
     * @param  array<int, string>|null $allowedLocales
     * @return array<string, string>
     */
    public function getTranslations(?string $key = null, ?array $allowedLocales = null): array
    {
        if ($key !== null) {
            $raw = $this->getAttributes()[$key] ?? null;

            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && $decoded !== []) {
                    $normalized = [];

                    foreach ($decoded as $locale => $value) {
                        $normalized[(string) $locale] = is_scalar($value) ? (string) $value : '';
                    }

                    return $normalized;
                }

                return [app()->getLocale() => $raw];
            }
        }

        $translations = $this->getTranslationsFromTrait($key, $allowedLocales);
        $normalized = [];

        foreach ($translations as $locale => $value) {
            $normalized[(string) $locale] = is_scalar($value) ? (string) $value : '';
        }

        return $normalized;
    }

    /**
     * Normalize boolean-like inputs from factories, forms, and casts.
     */
    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        if ($filtered !== null) {
            return $filtered;
        }

        return (bool) $value;
    }
}
