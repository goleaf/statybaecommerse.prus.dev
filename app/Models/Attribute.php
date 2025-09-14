<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\VisibleScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[ScopedBy([ActiveScope::class, EnabledScope::class, VisibleScope::class])]
final /**
 * Attribute
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Attribute extends Model
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

    // Additional helper methods
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->slug;
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ? strip_tags($this->description) : '';
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'text' => 'heroicon-o-document-text',
            'number' => 'heroicon-o-calculator',
            'boolean' => 'heroicon-o-check-circle',
            'select' => 'heroicon-o-list-bullet',
            'multiselect' => 'heroicon-o-squares-2x2',
            'color' => 'heroicon-o-swatch',
            'date' => 'heroicon-o-calendar',
            'textarea' => 'heroicon-o-document',
            'file' => 'heroicon-o-paper-clip',
            'image' => 'heroicon-o-photo',
            default => 'heroicon-o-adjustments-horizontal',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'text' => 'gray',
            'number' => 'blue',
            'boolean' => 'green',
            'select' => 'yellow',
            'multiselect' => 'orange',
            'color' => 'purple',
            'date' => 'red',
            'textarea' => 'indigo',
            'file' => 'pink',
            'image' => 'rose',
            default => 'gray',
        };
    }

    public function getValidationRulesForForm(): array
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }
        
        if ($this->validation_rules) {
            $rules = array_merge($rules, $this->validation_rules);
        }
        
        return $rules;
    }

    public function getFormComponentConfig(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->name,
            'placeholder' => $this->placeholder,
            'help_text' => $this->help_text,
            'required' => $this->is_required,
            'validation_rules' => $this->getValidationRulesForForm(),
            'default_value' => $this->default_value,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'step_value' => $this->step_value,
            'options' => $this->isSelectType() ? $this->enabledValues->pluck('value', 'id')->toArray() : [],
        ];
    }

    public function isUsedInProducts(): bool
    {
        return $this->products()->exists();
    }

    public function getUsageCount(): int
    {
        return $this->products()->count();
    }

    public function getValuesUsageCount(): int
    {
        return $this->values()->whereHas('variants')->count();
    }

    public function getMostUsedValue()
    {
        return $this->values()
            ->withCount('variants')
            ->orderBy('variants_count', 'desc')
            ->first();
    }

    public function getLeastUsedValue()
    {
        return $this->values()
            ->withCount('variants')
            ->orderBy('variants_count', 'asc')
            ->first();
    }

    public function getAverageValuesPerProduct(): float
    {
        $totalProducts = $this->getUsageCount();
        if ($totalProducts === 0) {
            return 0;
        }
        
        $totalValues = $this->values()->whereHas('variants')->count();
        return round($totalValues / $totalProducts, 2);
    }

    public function getPopularityScore(): int
    {
        $usageCount = $this->getUsageCount();
        $valuesCount = $this->getValuesCount();
        $enabledValuesCount = $this->getEnabledValuesCount();
        
        // Calculate popularity based on usage and values
        $score = ($usageCount * 10) + ($valuesCount * 2) + ($enabledValuesCount * 1);
        
        return min($score, 100); // Cap at 100
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_enabled) {
            return 'disabled';
        }
        
        if ($this->is_required) {
            return 'required';
        }
        
        if ($this->is_filterable) {
            return 'filterable';
        }
        
        return 'standard';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_badge) {
            'disabled' => 'gray',
            'required' => 'red',
            'filterable' => 'blue',
            'standard' => 'green',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_badge) {
            'disabled' => __('attributes.disabled'),
            'required' => __('attributes.required'),
            'filterable' => __('attributes.filterable'),
            'standard' => __('attributes.standard'),
            default => __('attributes.unknown'),
        };
    }

    public function duplicateForGroup(string $newGroupName): self
    {
        $duplicate = $this->replicate();
        $duplicate->group_name = $newGroupName;
        $duplicate->name = $this->name . ' (Copy)';
        $duplicate->slug = $this->slug . '-copy';
        $duplicate->save();
        
        // Duplicate values
        foreach ($this->values as $value) {
            $valueDuplicate = $value->replicate();
            $valueDuplicate->attribute_id = $duplicate->id;
            $valueDuplicate->save();
        }
        
        return $duplicate;
    }

    public function mergeWith(Attribute $otherAttribute): self
    {
        // Move all values from other attribute to this one
        $otherAttribute->values()->update(['attribute_id' => $this->id]);
        
        // Update product_attributes records to use this attribute instead
        // We need to update both attribute_id and attribute_value_id
        $otherAttribute->products()->get()->each(function ($product) use ($otherAttribute) {
            // Get the attribute values for this product from the other attribute
            $attributeValues = $otherAttribute->values()->whereHas('variants', function ($query) use ($product) {
                $query->whereHas('product', function ($q) use ($product) {
                    $q->where('id', $product->id);
                });
            })->get();
            
            // Update the product_attributes records
            foreach ($attributeValues as $value) {
                \DB::table('product_attributes')
                    ->where('product_id', $product->id)
                    ->where('attribute_id', $otherAttribute->id)
                    ->where('attribute_value_id', $value->id)
                    ->update(['attribute_id' => $this->id]);
            }
        });
        
        // Delete the other attribute
        $otherAttribute->delete();
        
        return $this;
    }

    // Advanced Translation Methods
    public function getTranslatedName(?string $locale = null): ?string
    {
        $translated = $this->trans('name', $locale);
        return $translated ?: $this->name;
    }


    // Scope for translated attributes
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Translation Management Methods
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->unique()->values()->toArray();
    }

    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    public function getOrCreateTranslation(string $locale): \App\Models\Translations\AttributeTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => $this->name,
            ]
        );
    }

    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);
        return $translation->update($data);
    }

    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        return true;
    }

    // Helper Methods
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $type = $this->getFormattedTypeAttribute();
        return "{$name} ({$type})";
    }

    public function getAttributeInfo(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'description' => $this->description,
            'group_name' => $this->group_name,
            'sort_order' => $this->sort_order,
            'is_required' => $this->is_required,
            'is_filterable' => $this->is_filterable,
            'is_searchable' => $this->is_searchable,
            'is_visible' => $this->is_visible,
            'is_editable' => $this->is_editable,
            'is_sortable' => $this->is_sortable,
            'is_enabled' => $this->is_enabled,
        ];
    }

    public function getTechnicalInfo(): array
    {
        return [
            'type' => $this->type,
            'default_value' => $this->default_value,
            'validation_rules' => $this->validation_rules,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'step_value' => $this->step_value,
            'placeholder' => $this->placeholder,
            'help_text' => $this->help_text,
            'icon' => $this->icon,
            'color' => $this->color,
            'meta_data' => $this->meta_data,
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'usage_count' => $this->getUsageCount(),
            'values_count' => $this->getValuesCount(),
            'enabled_values_count' => $this->getEnabledValuesCount(),
            'popularity_score' => $this->getPopularityScore(),
            'average_values_per_product' => $this->getAverageValuesPerProduct(),
            'status' => $this->status_badge,
            'status_color' => $this->status_color,
            'status_label' => $this->status_label,
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getAttributeInfo(),
            $this->getTechnicalInfo(),
            $this->getBusinessInfo(),
            [
                'translations' => $this->getAvailableLocales(),
                'has_translations' => count($this->getAvailableLocales()) > 0,
                'formatted_type' => $this->getFormattedTypeAttribute(),
                'type_icon' => $this->getTypeIconAttribute(),
                'type_color' => $this->getTypeColorAttribute(),
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ]
        );
    }

    public function getStatistics(): array
    {
        return [
            'usage_count' => $this->getUsageCount(),
            'values_count' => $this->getValuesCount(),
            'enabled_values_count' => $this->getEnabledValuesCount(),
            'popularity_score' => $this->getPopularityScore(),
            'average_values_per_product' => $this->getAverageValuesPerProduct(),
            'most_used_value' => $this->getMostUsedValue(),
            'least_used_value' => $this->getLeastUsedValue(),
            'status' => $this->status_badge,
            'status_color' => $this->status_color,
            'status_label' => $this->status_label,
        ];
    }
}
