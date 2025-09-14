<?php

namespace App\Models\Translations;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttributeValueTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class AttributeValueTranslation extends Model
{
    use HasFactory;

    protected $table = 'attribute_value_translations';

    protected $guarded = [];

    public $timestamps = true;

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    // Scopes
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    public function scopeByAttributeValue($query, int $attributeValueId)
    {
        return $query->where('attribute_value_id', $attributeValueId);
    }

    public function scopeWithValue($query)
    {
        return $query->whereNotNull('value')->where('value', '!=', '');
    }

    public function scopeWithDescription($query)
    {
        return $query->whereNotNull('description')->where('description', '!=', '');
    }

    // Accessors
    public function getFormattedValueAttribute(): string
    {
        return $this->value ?: __('attributes.untitled_value');
    }

    public function getFormattedDescriptionAttribute(): ?string
    {
        return $this->description;
    }

    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }

    // Helper methods
    public function hasValue(): bool
    {
        return !empty($this->value);
    }

    public function hasDescription(): bool
    {
        return !empty($this->description);
    }

    public function hasMetaData(): bool
    {
        return !empty($this->meta_data);
    }

    public function isEmpty(): bool
    {
        return !$this->hasValue() && !$this->hasDescription() && !$this->hasMetaData();
    }

    public function isComplete(): bool
    {
        return $this->hasValue() && $this->hasDescription();
    }

    // Static methods
    public static function getByAttributeValueAndLocale(int $attributeValueId, string $locale): ?self
    {
        return self::where('attribute_value_id', $attributeValueId)
            ->where('locale', $locale)
            ->first();
    }

    public static function getOrCreateForAttributeValueAndLocale(int $attributeValueId, string $locale): self
    {
        return self::firstOrCreate(
            [
                'attribute_value_id' => $attributeValueId,
                'locale' => $locale,
            ],
            [
                'value' => '',
                'description' => null,
                'meta_data' => null,
            ]
        );
    }

    public static function getTranslationsForAttributeValue(int $attributeValueId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('attribute_value_id', $attributeValueId)->get();
    }

    public static function getAvailableLocalesForAttributeValue(int $attributeValueId): array
    {
        return self::where('attribute_value_id', $attributeValueId)
            ->pluck('locale')
            ->toArray();
    }

    public static function getMissingLocalesForAttributeValue(int $attributeValueId, array $supportedLocales): array
    {
        $availableLocales = self::getAvailableLocalesForAttributeValue($attributeValueId);
        return array_diff($supportedLocales, $availableLocales);
    }
}
