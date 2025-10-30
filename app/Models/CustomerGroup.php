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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

/**
 * CustomerGroup Model
 *
 * Represents a customer segmentation group with discount rules, permissions, and access control.
 * Groups can be assigned to users/customers to provide special pricing, catalog access, and ordering capabilities.
 *
 * @property int                       $id
 * @property string                    $name
 * @property string                    $slug
 * @property string|null               $code
 * @property string|null               $color
 * @property string|null               $icon
 * @property string|null               $description
 * @property float|null                $discount_percentage
 * @property string|null               $discount_fixed
 * @property bool                      $has_special_pricing
 * @property bool                      $has_volume_discounts
 * @property bool                      $can_view_prices
 * @property bool                      $can_place_orders
 * @property bool                      $can_view_catalog
 * @property bool                      $can_use_coupons
 * @property bool                      $is_enabled
 * @property bool                      $is_active
 * @property bool                      $is_default
 * @property int                       $sort_order
 * @property string                    $type
 * @property array<string, mixed>|null $metadata
 * @property array<string, mixed>|null $conditions
 * @property string|null               $payment_terms
 * @property string|null               $minimum_order_amount
 * @property string|null               $credit_limit
 * @property \Carbon\Carbon            $created_at
 * @property \Carbon\Carbon            $updated_at
 * @property \Carbon\Carbon|null       $deleted_at
 * @property-read int                                                               $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User>              $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User>              $customers
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Discount>          $discounts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceList>         $priceLists
 *
 * @method static Builder|CustomerGroup active()
 * @method static Builder|CustomerGroup inactive()
 * @method static Builder|CustomerGroup enabled()
 * @method static Builder|CustomerGroup disabled()
 * @method static Builder|CustomerGroup withDiscount()
 * @method static Builder|CustomerGroup withSpecialPricing()
 * @method static Builder|CustomerGroup byType(string $type)
 * @method static Builder|CustomerGroup default()
 * @method static Builder|CustomerGroup orderByPriority()
 * @method static Builder|CustomerGroup orderedByName(?string $locale = null)
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

    /**
     * Provide a typed lookup for group level permission toggles so helper
     * methods can translate friendly keys (e.g. `view_prices`) into the
     * persisted column names without duplicating string literals everywhere.
     *
     * @var array<string, string>
     */
    private const PERMISSION_ATTRIBUTE_MAP = [
        'view_prices'  => 'can_view_prices',
        'place_orders' => 'can_place_orders',
        'view_catalog' => 'can_view_catalog',
        'use_coupons'  => 'can_use_coupons',
    ];

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
        'minimum_order_amount',
        'credit_limit',
        'payment_terms',
    ];

    /**
     * Bootstrap the model and ensure the slug column is automatically maintained.
     */
    protected static function booted(): void
    {
        // Generate a slug automatically when creating a record
        self::creating(static function (CustomerGroup $group): void {
            if (! $group->slug) {
                $group->slug = self::generateSlug($group);
            }
        });

        // Update slug when name or code changes
        self::updating(static function (CustomerGroup $group): void {
            if ($group->isDirty(['name', 'code']) && empty($group->slug)) {
                $group->slug = self::generateSlug($group);
            }
        });

        // Ensure translatable fields are properly stored
        self::saving(static function (CustomerGroup $group): void {
            foreach ($group->translatable as $attribute) {
                $value = $group->getAttribute($attribute);

                if (is_array($value)) {
                    continue;
                }

                if ($value === null) {
                    // Avoid clobbering translated JSON columns with empty payloads
                    // so that existing locale specific content stays intact.
                    continue;
                }

                if (! is_scalar($value)) {
                    // Avoid clobbering translated JSON columns with empty payloads
                    // so that existing locale specific content stays intact.
                    continue;
                }

                $stringValue = trim((string) $value);

                if ($stringValue === '') {
                    continue;
                }

                $group->setTranslation($attribute, 'lt', $stringValue);
                $group->setTranslation($attribute, 'en', $stringValue);
            }

            $rawTerms = $group->getAttribute('payment_terms');

            if ($rawTerms === null) {
                $group->setAttribute('payment_terms', 'net_30');
            } elseif (is_scalar($rawTerms)) {
                $normalizedTerms = Str::of((string) $rawTerms)
                    ->lower()
                    ->replace(' ', '_')
                    ->replace('-', '_')
                    ->value();

                $group->setAttribute('payment_terms', $normalizedTerms !== '' ? $normalizedTerms : 'net_30');
            } else {
                $group->setAttribute('payment_terms', 'net_30');
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
            'minimum_order_amount' => 'decimal:2',
            'credit_limit'         => 'decimal:2',
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

    /** Handle users functionality with proper error handling. */

    /**
     * @return BelongsToMany<User, self>
     */
    public function users(): BelongsToMany
    {
        /** @var BelongsToMany<User, CustomerGroup> $relation */
        $relation = $this
            ->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')
            ->withPivot('assigned_at')
            // Retain the default pivot timestamps alongside the custom assignment
            // marker so auditing remains comprehensive for customer segmentation.
            ->withTimestamps();

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
        $relation = $this
            ->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')
            ->withPivot('assigned_at')
            ->withTimestamps();

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
        return $this->applyBooleanScope($query, 'is_enabled', true);
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
        // Match either percentage based or fixed discounts so B2B pricing rules
        // surface every configured incentive when building dashboards.
        $percentageColumn = $query->qualifyColumn('discount_percentage');
        $fixedColumn = $query->qualifyColumn('discount_fixed');

        return $query->where(static function (Builder $innerQuery) use ($percentageColumn, $fixedColumn): void {
            $innerQuery
                ->whereRaw("COALESCE($percentageColumn, 0) + 0 > 0")
                ->orWhereRaw("COALESCE($fixedColumn, 0) + 0 > 0");
        });
    }

    /**
     * Scope to get only inactive groups.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeInactive(Builder $query): Builder
    {
        $query = $this->applyBooleanScope($query, 'is_enabled', false);

        return $this->applyBooleanScope($query, 'is_active', false);
    }

    /**
     * Scope to get only active groups (both is_enabled and is_active).
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeActive(Builder $query): Builder
    {
        $query = $this->applyBooleanScope($query, 'is_enabled', true);

        return $this->applyBooleanScope($query, 'is_active', true);
    }

    /**
     * Scope to get only disabled groups.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeDisabled(Builder $query): Builder
    {
        return $this->applyBooleanScope($query, 'is_enabled', false);
    }

    /**
     * Scope to get groups with special pricing enabled.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeWithSpecialPricing(Builder $query): Builder
    {
        return $this->applyBooleanScope($query, 'has_special_pricing', true);
    }

    /**
     * Scope to filter by group type.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get only default groups.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $this->applyBooleanScope($query, 'is_default', true);
    }

    /**
     * Scope to order by sort_order ascending.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        $sortColumn = $query->qualifyColumn('sort_order');
        $keyColumn = $query->getModel()->getQualifiedKeyName();

        return $query
            ->orderByRaw("CASE WHEN $sortColumn IS NULL THEN 1 ELSE 0 END")
            ->orderBy($sortColumn)
            ->orderBy($keyColumn);
    }

    /**
     * Order customer groups alphabetically for deterministic dropdowns and reports.
     *
     * @param  Builder<CustomerGroup> $query
     * @return Builder<CustomerGroup>
     */
    public function scopeOrderedByName(Builder $query, ?string $locale = null): Builder
    {
        // Fall back to the current application locale so translated JSON columns can be queried safely.
        $resolvedLocale = $locale ?? app()->getLocale();

        // Strip any unexpected characters from the locale to avoid malformed JSON path fragments.
        $resolvedLocale = preg_replace('/[^A-Za-z0-9_]/', '_', (string) $resolvedLocale);

        // Guarantee a sensible default even when the sanitised locale becomes empty after filtering.
        $resolvedLocale = $resolvedLocale !== '' ? $resolvedLocale : 'en';

        // Use JSON path ordering to support Spatie translatable columns while preserving a stable tie-breaker.
        return $query
            ->orderBy("name->{$resolvedLocale}")
            ->orderBy('id');
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

        if ($normalized === false) {
            // Respect explicit `is_enabled` intents supplied in the same payload while still
            // syncing legacy single-flag writes. When no `is_enabled` value has been set we
            // cascade the disable, otherwise we only mirror a falsey counterpart so callers can
            // intentionally keep `is_enabled` true for scheduled downtime scenarios.
            $hasExplicitEnabled = array_key_exists('is_enabled', $this->attributes);
            $existingEnabled = $hasExplicitEnabled ? $this->attributes['is_enabled'] : null;

            if (
                ! $hasExplicitEnabled
                || ! $this->isDirty('is_enabled')
                || $this->normalizeBoolean($existingEnabled) === false
            ) {
                // Allow single-flag updates (where callers only touch `is_active`) to cascade
                // the disable, but keep divergent payloads intact when an explicit `is_enabled`
                // value has been provided in the same request lifecycle.
                $this->attributes['is_enabled'] = false;
            }

            return;
        }

        // Only mirror when the counterpart is unset or already aligned so callers
        // can deliberately diverge `is_active` from `is_enabled` without the later
        // setter overwriting their intent during mass-assignment.
        $existingEnabled = $this->attributes['is_enabled'] ?? null;

        if ($existingEnabled === null || $this->normalizeBoolean($existingEnabled) === $normalized) {
            $this->attributes['is_enabled'] = $normalized;
        }
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

        if ($normalized === false) {
            // Mirror the active mutator guard so explicit `is_active` overrides remain intact
            // when both flags are provided. We only cascade the disable when no `is_active`
            // value has been supplied or when it already evaluates to false.
            $hasExplicitActive = array_key_exists('is_active', $this->attributes);
            $existingActive = $hasExplicitActive ? $this->attributes['is_active'] : null;

            if (
                ! $hasExplicitActive
                || ! $this->isDirty('is_active')
                || $this->normalizeBoolean($existingActive) === false
            ) {
                // Keep the mirrored behaviour symmetrical so that single-flag updates continue
                // disabling both columns while preserving deliberate divergence during combined
                // mass-assignment operations handled by tests and Filament resources.
                $this->attributes['is_active'] = false;
            }

            return;
        }

        // Avoid clobbering explicit `is_active` assignments supplied in the same
        // payload by only mirroring when no value has been set yet or the values
        // already match.
        $existingActive = $this->attributes['is_active'] ?? null;

        if ($existingActive === null || $this->normalizeBoolean($existingActive) === $normalized) {
            $this->attributes['is_active'] = $normalized;
        }
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

    /**
     * Apply a tolerant boolean comparison so legacy truthy/falsey values stay queryable.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    private function applyBooleanScope(Builder $query, string $column, bool $expected): Builder
    {
        $qualifiedColumn = $query->qualifyColumn($column);

        $normalizedCandidates = $expected
            ? [1, true, '1']
            : [0, false, '0', ''];
        $normalizedCandidates = array_values(array_unique($normalizedCandidates, SORT_REGULAR));

        $stringCandidates = $expected
            ? ['true', 't', 'yes', 'on']
            : ['false', 'f', 'no', 'off'];

        $normalizedStringCandidates = array_values(array_unique(array_map(
            static fn (string $value): string => // Normalise spacing and casing so legacy datasets using mixed casing
                // (e.g. "TrUe" or " Off ") continue to be interpreted correctly.
                strtolower(trim($value)),
            array_merge(
                array_map(
                    static fn (mixed $value): string => strtolower(trim((string) $value)),
                    $normalizedCandidates
                ),
                $stringCandidates
            )
        )));

        return $query->where(static function (Builder $booleanQuery) use ($qualifiedColumn, $expected, $normalizedCandidates, $normalizedStringCandidates): void {
            $booleanQuery->whereIn($qualifiedColumn, $normalizedCandidates);

            $booleanQuery->orWhereIn(
                DB::raw(sprintf('LOWER(TRIM(CAST(%s AS CHAR)))', $qualifiedColumn)),
                $normalizedStringCandidates
            );

            if (! $expected) {
                $booleanQuery->orWhereNull($qualifiedColumn);
            }
        });
    }

    /**
     * Check if the group has any discount (percentage or fixed).
     */
    public function hasAnyDiscount(): bool
    {
        return $this->hasDiscountRate() || $this->hasFixedDiscount();
    }

    /**
     * Check if the group has a fixed discount.
     */
    public function hasFixedDiscount(): bool
    {
        $rawFixed = $this->getAttribute('discount_fixed');

        if (! is_numeric($rawFixed)) {
            return false;
        }

        return (float) $rawFixed > 0;
    }

    /**
     * Check if the group allows catalog viewing.
     */
    public function canViewCatalog(): bool
    {
        return (bool) $this->getAttribute('can_view_catalog');
    }

    /**
     * Check if the group allows price viewing.
     */
    public function canViewPrices(): bool
    {
        return (bool) $this->getAttribute('can_view_prices');
    }

    /**
     * Check if the group allows placing orders.
     */
    public function canPlaceOrders(): bool
    {
        return (bool) $this->getAttribute('can_place_orders');
    }

    /**
     * Check if the group can use coupons.
     */
    public function canUseCoupons(): bool
    {
        return (bool) $this->getAttribute('can_use_coupons');
    }

    /**
     * Check if this is the default group.
     */
    public function isDefault(): bool
    {
        return (bool) $this->getAttribute('is_default');
    }

    /**
     * Get total discount (considering both percentage and fixed).
     */
    public function getTotalDiscountForAmount(float $amount): float
    {
        $discount = 0.0;

        if ($this->hasDiscountRate()) {
            $percentage = $this->getAttribute('discount_percentage');
            if (is_numeric($percentage)) {
                $discount += ($amount * ((float) $percentage) / 100);
            }
        }

        if ($this->hasFixedDiscount()) {
            $fixed = $this->getAttribute('discount_fixed');
            if (is_numeric($fixed)) {
                $discount += (float) $fixed;
            }
        }

        return $discount;
    }

    /**
     * Check if the group has volume discounts enabled.
     */
    public function hasVolumeDiscounts(): bool
    {
        return (bool) $this->getAttribute('has_volume_discounts');
    }

    /**
     * Check if the group has special pricing enabled.
     */
    public function hasSpecialPricing(): bool
    {
        return (bool) $this->getAttribute('has_special_pricing');
    }

    /**
     * Determine whether the customer group enforces a positive credit limit.
     */
    public function hasCreditLimit(): bool
    {
        $creditLimit = $this->getAttribute('credit_limit');

        if ($creditLimit === null || $creditLimit === '') {
            // Missing credit limit means the group can spend without a ceiling.
            return false;
        }

        if (! is_numeric($creditLimit)) {
            return false;
        }

        $numericLimit = (float) $creditLimit;

        return $numericLimit > 0.0;
    }

    /**
     * Fetch the numeric credit limit, normalising it to a float for calculations.
     */
    public function getCreditLimitAmount(): ?float
    {
        $creditLimit = $this->getAttribute('credit_limit');

        if (! is_numeric($creditLimit)) {
            return null;
        }

        $numericLimit = (float) $creditLimit;

        if ($numericLimit <= 0.0) {
            return null;
        }

        // Round for deterministic comparisons in tests and reporting exports.
        return round($numericLimit, 2);
    }

    /**
     * Flag whether the group requires a minimum order amount before checkout.
     */
    public function requiresMinimumOrderAmount(): bool
    {
        $minimum = $this->getAttribute('minimum_order_amount');

        if ($minimum === null || $minimum === '') {
            return false;
        }

        if (! is_numeric($minimum)) {
            return false;
        }

        $numericMinimum = (float) $minimum;

        return $numericMinimum > 0.0;
    }

    /**
     * Retrieve the minimum order threshold as a float for downstream validation.
     */
    public function getMinimumOrderAmount(): ?float
    {
        $minimum = $this->getAttribute('minimum_order_amount');

        if (! is_numeric($minimum)) {
            return null;
        }

        $numericMinimum = (float) $minimum;

        if ($numericMinimum <= 0.0) {
            return null;
        }

        return round($numericMinimum, 2);
    }

    /**
     * Resolve configured payment terms such as "Net 30" or "Net 60".
     */
    public function getPaymentTerms(): ?string
    {
        $terms = $this->getAttribute('payment_terms');

        if ($terms === null) {
            return null;
        }

        if (! is_scalar($terms)) {
            return null;
        }

        $normalised = trim((string) $terms);

        if ($normalised === '') {
            return null;
        }

        if (preg_match('/^net[_\s-]?(\d{1,3})$/i', $normalised, $matches) === 1) {
            return 'Net ' . $matches[1];
        }

        return $normalised;
    }

    /**
     * Check group specific permissions to drive catalogue access rules.
     */
    public function hasPermission(string $permission): bool
    {
        $attribute = self::PERMISSION_ATTRIBUTE_MAP[$permission] ?? $permission;

        // Delegate to getAttribute so the cast layer keeps booleans consistent.
        $value = $this->getAttribute($attribute);

        return $this->normalizeBoolean($value);
    }
}
