<?php

declare (strict_types=1);
namespace App\Models\Translations;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * AttributeTranslation
 * 
 * Eloquent model representing the AttributeTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $factory
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeTranslation query()
 * @mixin \Eloquent
 */
final class AttributeTranslation extends Model
{
    use HasFactory;
    protected static string $factory = \Database\Factories\AttributeTranslationFactory::class;
    protected $table = 'attribute_translations';
    protected $fillable = ['attribute_id', 'locale', 'name'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['attribute_id' => 'integer'];
    }
    /**
     * Handle attribute functionality with proper error handling.
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
    // Scopes
    /**
     * Handle scopeByLocale functionality with proper error handling.
     * @param mixed $query
     * @param string $locale
     */
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }
    /**
     * Handle scopeByAttribute functionality with proper error handling.
     * @param mixed $query
     * @param int $attributeId
     */
    public function scopeByAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }
    /**
     * Handle scopeWithName functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithName($query)
    {
        return $query->whereNotNull('name');
    }
    // Accessors
    /**
     * Handle getFormattedNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedNameAttribute(): string
    {
        return $this->name ?: __('attributes.untitled_attribute');
    }
    // Helper methods
    /**
     * Handle hasName functionality with proper error handling.
     * @return bool
     */
    public function hasName(): bool
    {
        return !empty($this->name);
    }
    /**
     * Handle isEmpty functionality with proper error handling.
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !$this->hasName();
    }
    /**
     * Handle isComplete functionality with proper error handling.
     * @return bool
     */
    public function isComplete(): bool
    {
        return $this->hasName();
    }
    // Static helper methods
    /**
     * Handle getByAttributeAndLocale functionality with proper error handling.
     * @param int $attributeId
     * @param string $locale
     * @return self|null
     */
    public static function getByAttributeAndLocale(int $attributeId, string $locale): ?self
    {
        return self::where('attribute_id', $attributeId)->where('locale', $locale)->first();
    }
    /**
     * Handle getOrCreateForAttributeAndLocale functionality with proper error handling.
     * @param int $attributeId
     * @param string $locale
     * @return self
     */
    public static function getOrCreateForAttributeAndLocale(int $attributeId, string $locale): self
    {
        return self::firstOrCreate(['attribute_id' => $attributeId, 'locale' => $locale], ['name' => '']);
    }
    /**
     * Handle getTranslationsForAttribute functionality with proper error handling.
     * @param int $attributeId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getTranslationsForAttribute(int $attributeId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('attribute_id', $attributeId)->orderBy('locale')->get();
    }
    /**
     * Handle getAvailableLocalesForAttribute functionality with proper error handling.
     * @param int $attributeId
     * @return array
     */
    public static function getAvailableLocalesForAttribute(int $attributeId): array
    {
        return self::where('attribute_id', $attributeId)->pluck('locale')->toArray();
    }
    /**
     * Handle getMissingLocalesForAttribute functionality with proper error handling.
     * @param int $attributeId
     * @param array $supportedLocales
     * @return array
     */
    public static function getMissingLocalesForAttribute(int $attributeId, array $supportedLocales): array
    {
        $existingLocales = self::getAvailableLocalesForAttribute($attributeId);
        return array_diff($supportedLocales, $existingLocales);
    }
}