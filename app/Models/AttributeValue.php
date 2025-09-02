<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class AttributeValue extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'value',
        'key',
        'position',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    protected string $translationModel = \App\Models\Translations\AttributeValueTranslation::class;

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'product_attribute_values')
            ->withTimestamps();
    }

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
        return $query->orderBy('position');
    }

    public function scopeForAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }
}
