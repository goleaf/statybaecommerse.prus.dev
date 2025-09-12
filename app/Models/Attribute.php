<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class Attribute extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'attributes';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'validation_rules',
        'default_value',
        'is_required',
        'is_filterable',
        'is_searchable',
        'is_visible',
        'is_editable',
        'is_sortable',
        'sort_order',
        'is_enabled',
        'category_id',
        'group_name',
        'icon',
        'color',
        'min_value',
        'max_value',
        'step_value',
        'placeholder',
        'help_text',
        'meta_data',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_searchable' => 'boolean',
            'is_visible' => 'boolean',
            'is_editable' => 'boolean',
            'is_sortable' => 'boolean',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'category_id' => 'integer',
            'min_value' => 'float',
            'max_value' => 'float',
            'step_value' => 'float',
            'validation_rules' => 'array',
            'meta_data' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\AttributeTranslation::class;

    protected $translatable = [
        'name',
        'slug', 
        'description',
        'placeholder',
        'help_text',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'product_attributes')
            ->withTimestamps();
    }

    public function variants(): BelongsToMany
    {
        return $this
            ->belongsToMany(ProductVariant::class, 'product_variant_attributes')
            ->withTimestamps();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productAttributeValues(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductAttributeValue::class,
            AttributeValue::class,
            'attribute_id',
            'attribute_value_id',
            'id',
            'id'
        );
    }

    public function enabledValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_enabled', true)->orderBy('sort_order');
    }

    public function requiredValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_required', true)->orderBy('sort_order');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    public function scopeSortable($query)
    {
        return $query->where('is_sortable', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    public function scopeWithValues($query)
    {
        return $query->with('values');
    }

    public function scopeWithEnabledValues($query)
    {
        return $query->with('enabledValues');
    }

    // Accessor methods
    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'text' => 'Text',
            'number' => 'Number',
            'boolean' => 'Boolean',
            'select' => 'Select',
            'multiselect' => 'Multi Select',
            'color' => 'Color',
            'date' => 'Date',
            'textarea' => 'Textarea',
            'file' => 'File',
            'image' => 'Image',
            default => ucfirst($this->type),
        };
    }

    public function getValidationRulesArrayAttribute(): array
    {
        return $this->validation_rules ?? [];
    }

    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }

    // Mutator methods
    public function setSlugAttribute(?string $value): void
    {
        $this->attributes['slug'] = $value ?: Str::slug($this->name);
    }

    public function setValidationRulesAttribute($value): void
    {
        $this->attributes['validation_rules'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setMetaDataAttribute($value): void
    {
        $this->attributes['meta_data'] = is_array($value) ? json_encode($value) : $value;
    }

    // Helper methods
    public function isSelectType(): bool
    {
        return in_array($this->type, ['select', 'multiselect']);
    }

    public function isNumericType(): bool
    {
        return in_array($this->type, ['number']);
    }

    public function isTextType(): bool
    {
        return in_array($this->type, ['text', 'textarea']);
    }

    public function isBooleanType(): bool
    {
        return $this->type === 'boolean';
    }

    public function isDateType(): bool
    {
        return $this->type === 'date';
    }

    public function isFileType(): bool
    {
        return in_array($this->type, ['file', 'image']);
    }

    public function getDefaultValueForType(): mixed
    {
        return match ($this->type) {
            'text', 'textarea' => '',
            'number' => 0,
            'boolean' => false,
            'select', 'multiselect' => null,
            'color' => '#000000',
            'date' => null,
            'file', 'image' => null,
            default => null,
        };
    }

    public function canHaveMultipleValues(): bool
    {
        return in_array($this->type, ['multiselect', 'file', 'image']);
    }

    public function getValuesCount(): int
    {
        return $this->values()->count();
    }

    public function getEnabledValuesCount(): int
    {
        return $this->enabledValues()->count();
    }
}
