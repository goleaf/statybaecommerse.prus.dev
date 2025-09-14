<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class AttributeValue extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'color_code',
        'sort_order',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected string $translationModel = \App\Models\Translations\AttributeValueTranslation::class;
    protected array $translatable = ['value', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['value', 'slug', 'is_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_attributes',
            'attribute_value_id',
            'variant_id'
        );
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    public function scopeByAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->attribute && $this->attribute->isColorType() && $this->color_code) {
            return $this->value . ' (' . $this->color_code . ')';
        }
        
        return $this->value;
    }

    public function getColorAttribute(): ?string
    {
        return $this->color_code;
    }

    public function isColor(): bool
    {
        return $this->attribute && $this->attribute->isColorType();
    }

    public function isSize(): bool
    {
        return $this->attribute && $this->attribute->slug === 'size';
    }

    public function isColorValue(): bool
    {
        return $this->attribute && $this->attribute->slug === 'color';
    }
}