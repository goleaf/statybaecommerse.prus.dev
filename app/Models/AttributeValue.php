<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Traits\HasTranslations;
use Database\Factories\AttributeValueFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as EloquentAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JsonSerializable;

/**
 * AttributeValue
 *
 * Eloquent model representing the AttributeValue entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed  $table
 * @property mixed  $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue query()
 *
 * @mixin \Eloquent
 *
 * @phpstan-use \Illuminate\Database\Eloquent\Factories\HasFactory<AttributeValueFactory>
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class AttributeValue extends Model
{
    /** @phpstan-ignore-next-line */
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Provide the shared alphabetical ordering scope based on the value column.
     */
    use OrdersByName;

    /**
     * Sort attribute values by their human readable value field when using the OrdersByName concern.
     */
    protected string $nameColumn = 'value';

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'attribute_value_type',
        'valueable_type',
        'valueable_id',
        'color_code',
        'sort_order',
        'is_enabled',
        'description',
        'hex_color',
        'image',
        'metadata',
        'display_value',
        'is_active',
        'is_default',
        'is_searchable',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'sort_order'    => 'integer',
            'is_enabled'    => 'boolean',
            'is_active'     => 'boolean',
            'is_default'    => 'boolean',
            'is_searchable' => 'boolean',
            'metadata'      => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\AttributeValueTranslation::class;

    /**
     * Handle attribute functionality with proper error handling.
     *
     * @return BelongsTo<Attribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Handle products functionality with proper error handling.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes', 'attribute_value_id', 'product_id')->withTimestamps();
    }

    /**
     * Handle variants functionality with proper error handling.
     *
     * @return BelongsToMany<ProductVariant, $this>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes', 'attribute_value_id', 'variant_id')->withTimestamps();
    }

    /**
     * Handle valueable functionality with proper error handling.
     *
     * @return MorphTo<Model, $this>
     */
    public function valueable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        // Limit the query to records explicitly flagged as enabled for storefront visibility.
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        // Keep attribute values sorted according to the configured manual sort order column.
        return $query->orderBy('sort_order');
    }

    /**
     * Handle scopeForAttribute functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeForAttribute(Builder $query, int $attributeId): Builder
    {
        // Filter records by the owning attribute identifier for quick relationship lookups.
        return $query->where('attribute_id', $attributeId);
    }

    /**
     * Handle scopeByAttribute functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeByAttribute(Builder $query, int $attributeId): Builder
    {
        // Provide an alias scope for Attribute::values() style queries that need explicit filtering.
        return $query->where('attribute_id', $attributeId);
    }

    /**
     * Handle scopeByValue functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeByValue(Builder $query, string $value): Builder
    {
        // Match records exactly on the stored value column to support deterministic lookups.
        return $query->where('value', $value);
    }

    /**
     * Handle scopeByDisplayValue functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeByDisplayValue(Builder $query, string $displayValue): Builder
    {
        // Allow callers to query by the localized display label that surfaces in the UI.
        return $query->where('display_value', $displayValue);
    }

    /**
     * Handle scopeByHexColor functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeByHexColor(Builder $query, string $hexColor): Builder
    {
        // Support color pickers by filtering on the stored hex value when available.
        return $query->where('hex_color', $hexColor);
    }

    /**
     * Handle scopeByImage functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeByImage(Builder $query, string $image): Builder
    {
        // Enable retrieval of values with an associated media image reference.
        return $query->where('image', $image);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  Builder<AttributeValue> $query
     * @return Builder<AttributeValue>
     */
    public function scopeActive(Builder $query): Builder
    {
        // Restrict the query to items marked as active to honour merchandising rules.
        return $query->where('is_active', true);
    }

    public function refresh(): static
    {
        if (! $this->exists) {
            return $this;
        }

        // Reload the model without the storefront-only scopes so records that
        // were just deactivated or disabled can still be rehydrated in tests
        // and Filament callbacks.
        $fresh = self::withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
            SoftDeletingScope::class,
        ])->whereKey($this->getKey())->first();

        if ($fresh === null) {
            // Bubble up the missing model scenario with a standard not-found exception for consistency.
            throw (new ModelNotFoundException)->setModel(self::class);
        }

        $this->setRawAttributes($fresh->getAttributes(), true);
        $this->setRelations($fresh->getRelations());
        $this->syncOriginal();

        return $this;
    }

    /**
     * Handle metadata accessor functionality with proper error handling.
     *
     * @return EloquentAttribute<array<array-key, mixed>, array<array-key, mixed>|null>
     */
    protected function metadata(): EloquentAttribute
    {
        return EloquentAttribute::make(
            /**
             * Normalise persisted metadata into an array payload for consumers.
             *
             * @return array<array-key, mixed>
             */
            get: static function ($value): array {
                if (is_array($value)) {
                    return $value;
                }

                if (is_string($value)) {
                    return safe_json_decode_array($value);
                }

                if ($value instanceof JsonSerializable) {
                    $encoded = $value->jsonSerialize();

                    return is_array($encoded) ? $encoded : [];
                }

                return $value ? (array) $value : [];
            },
            /**
             * Serialise incoming metadata into a format suitable for persistence.
             *
             * @param  array<array-key, mixed>|JsonSerializable|string|null $value
             * @return array<array-key, mixed>|null
             */
            set: static function ($value): ?array {
                if ($value === null) {
                    return null;
                }

                if (is_string($value)) {
                    $decoded = safe_json_decode_array($value);

                    return $decoded !== [] ? $decoded : null;
                }

                if ($value instanceof JsonSerializable) {
                    $encoded = $value->jsonSerialize();

                    return is_array($encoded) ? $encoded : null;
                }

                return is_array($value) ? $value : (array) $value;
            }
        );
    }
}
