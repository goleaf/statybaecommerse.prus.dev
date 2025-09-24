<?php

namespace App\Models\Translations;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttributeValueTranslation
 *
 * Eloquent model representing the AttributeValueTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $guarded
 * @property mixed $timestamps
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation query()
 *
 * @mixin \Eloquent
 */
class AttributeValueTranslation extends Model
{
    use HasFactory;

    protected $table = 'attribute_value_translations';

    protected $guarded = [];

    public $timestamps = true;

    protected $casts = ['meta_data' => 'array'];

    /**
     * Handle attributeValue functionality with proper error handling.
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    // Scopes
    /**
     * Handle scopeByLocale functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Handle scopeByAttributeValue functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByAttributeValue($query, int $attributeValueId)
    {
        return $query->where('attribute_value_id', $attributeValueId);
    }

    /**
     * Handle scopeWithValue functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithValue($query)
    {
        return $query->whereNotNull('value')->where('value', '!=', '');
    }

    /**
     * Handle scopeWithDescription functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithDescription($query)
    {
        return $query->whereNotNull('description')->where('description', '!=', '');
    }

    // Accessors
    /**
     * Handle getFormattedValueAttribute functionality with proper error handling.
     */
    public function getFormattedValueAttribute(): string
    {
        return $this->value ?: __('attributes.untitled_value');
    }

    /**
     * Handle getFormattedDescriptionAttribute functionality with proper error handling.
     */
    public function getFormattedDescriptionAttribute(): ?string
    {
        return $this->description;
    }

    /**
     * Handle getMetaDataArrayAttribute functionality with proper error handling.
     */
    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }

    // Helper methods
    /**
     * Handle hasValue functionality with proper error handling.
     */
    public function hasValue(): bool
    {
        return ! empty($this->value);
    }

    /**
     * Handle hasDescription functionality with proper error handling.
     */
    public function hasDescription(): bool
    {
        return ! empty($this->description);
    }

    /**
     * Handle hasMetaData functionality with proper error handling.
     */
    public function hasMetaData(): bool
    {
        return ! empty($this->meta_data);
    }

    /**
     * Handle isEmpty functionality with proper error handling.
     */
    public function isEmpty(): bool
    {
        return ! $this->hasValue() && ! $this->hasDescription() && ! $this->hasMetaData();
    }

    /**
     * Handle isComplete functionality with proper error handling.
     */
    public function isComplete(): bool
    {
        return $this->hasValue() && $this->hasDescription();
    }

    // Static methods
    /**
     * Handle getByAttributeValueAndLocale functionality with proper error handling.
     */
    public static function getByAttributeValueAndLocale(int $attributeValueId, string $locale): ?self
    {
        return self::where('attribute_value_id', $attributeValueId)->where('locale', $locale)->first();
    }

    /**
     * Handle getOrCreateForAttributeValueAndLocale functionality with proper error handling.
     */
    public static function getOrCreateForAttributeValueAndLocale(int $attributeValueId, string $locale): self
    {
        return self::firstOrCreate(['attribute_value_id' => $attributeValueId, 'locale' => $locale], ['value' => '', 'description' => null, 'meta_data' => null]);
    }

    /**
     * Handle getTranslationsForAttributeValue functionality with proper error handling.
     *
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getTranslationsForAttributeValue(int $attributeValueId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('attribute_value_id', $attributeValueId)->get();
    }

    /**
     * Handle getAvailableLocalesForAttributeValue functionality with proper error handling.
     */
    public static function getAvailableLocalesForAttributeValue(int $attributeValueId): array
    {
        return self::where('attribute_value_id', $attributeValueId)->pluck('locale')->toArray();
    }

    /**
     * Handle getMissingLocalesForAttributeValue functionality with proper error handling.
     */
    public static function getMissingLocalesForAttributeValue(int $attributeValueId, array $supportedLocales): array
    {
        $availableLocales = self::getAvailableLocalesForAttributeValue($attributeValueId);

        return array_diff($supportedLocales, $availableLocales);
    }
}
