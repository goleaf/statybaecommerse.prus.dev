<?php

declare(strict_types=1);

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

#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final /**
 * AttributeValue
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class AttributeValue extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'color_code',
        'sort_order',
        'is_enabled',
        'description',
        'hex_color',
        'image',
        'metadata',
        'display_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\AttributeValueTranslation::class;

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    // Note: AttributeValue doesn't have a direct products() relationship
    // Products are connected through variants via product_variant_attributes table

    public function variants(): BelongsToMany
    {
        return $this
            ->belongsToMany(ProductVariant::class, 'product_variant_attributes', 'attribute_value_id', 'variant_id')
            ->withTimestamps();
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeForAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    public function scopeByAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    public function scopeByValue($query, string $value)
    {
        return $query->where('value', $value);
    }

    public function scopeByDisplayValue($query, string $displayValue)
    {
        return $query->where('display_value', $displayValue);
    }

    public function scopeByHexColor($query, string $hexColor)
    {
        return $query->where('hex_color', $hexColor);
    }

    public function scopeByImage($query, string $image)
    {
        return $query->where('image', $image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
