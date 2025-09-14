<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * VariantAttributeValue
 * 
 * Enhanced model for managing variant attribute values with multi-language support and advanced filtering.
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class VariantAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'attribute_id',
        'attribute_name',
        'attribute_value',
        'attribute_value_display',
        'attribute_value_lt',
        'attribute_value_en',
        'attribute_value_slug',
        'sort_order',
        'is_filterable',
        'is_searchable',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_searchable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the variant that owns the attribute value.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the attribute that owns the attribute value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    /**
     * Get the localized attribute value.
     */
    public function getLocalizedValue(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        
        return match ($locale) {
            'lt' => $this->attribute_value_lt ?: $this->attribute_value_display ?: $this->attribute_value,
            'en' => $this->attribute_value_en ?: $this->attribute_value_display ?: $this->attribute_value,
            default => $this->attribute_value_display ?: $this->attribute_value,
        };
    }

    /**
     * Get the display value for the current locale.
     */
    public function getDisplayValueAttribute(): string
    {
        return $this->getLocalizedValue();
    }

    /**
     * Scope to filter by attribute.
     */
    public function scopeByAttribute($query, string $attributeName)
    {
        return $query->where('attribute_name', $attributeName);
    }

    /**
     * Scope to filter by attribute value.
     */
    public function scopeByValue($query, string $value)
    {
        return $query->where('attribute_value', $value);
    }

    /**
     * Scope to get filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope to get searchable attributes.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope to get attributes for specific variant.
     */
    public function scopeForVariant($query, int $variantId)
    {
        return $query->where('variant_id', $variantId);
    }
}
