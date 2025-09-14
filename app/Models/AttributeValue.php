<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * AttributeValue
 * 
 * Eloquent model representing the AttributeValue entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class AttributeValue extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $table = 'attribute_values';
    protected $fillable = ['attribute_id', 'value', 'slug', 'color_code', 'sort_order', 'is_enabled', 'description', 'hex_color', 'image', 'metadata', 'display_value', 'is_active'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_enabled' => 'boolean', 'is_active' => 'boolean', 'metadata' => 'array'];
    }
    protected string $translationModel = \App\Models\Translations\AttributeValueTranslation::class;
    /**
     * Handle attribute functionality with proper error handling.
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
    // Note: AttributeValue doesn't have a direct products() relationship
    // Products are connected through variants via product_variant_attributes table
    /**
     * Handle variants functionality with proper error handling.
     * @return BelongsToMany
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes', 'attribute_value_id', 'variant_id')->withTimestamps();
    }
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
    /**
     * Handle scopeForAttribute functionality with proper error handling.
     * @param mixed $query
     * @param int $attributeId
     */
    public function scopeForAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
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
     * Handle scopeByValue functionality with proper error handling.
     * @param mixed $query
     * @param string $value
     */
    public function scopeByValue($query, string $value)
    {
        return $query->where('value', $value);
    }
    /**
     * Handle scopeByDisplayValue functionality with proper error handling.
     * @param mixed $query
     * @param string $displayValue
     */
    public function scopeByDisplayValue($query, string $displayValue)
    {
        return $query->where('display_value', $displayValue);
    }
    /**
     * Handle scopeByHexColor functionality with proper error handling.
     * @param mixed $query
     * @param string $hexColor
     */
    public function scopeByHexColor($query, string $hexColor)
    {
        return $query->where('hex_color', $hexColor);
    }
    /**
     * Handle scopeByImage functionality with proper error handling.
     * @param mixed $query
     * @param string $image
     */
    public function scopeByImage($query, string $image)
    {
        return $query->where('image', $image);
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}