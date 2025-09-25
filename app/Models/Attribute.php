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

/**
 * Attribute
 *
 * Eloquent model representing the Attribute entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $appends
 * @property string $translationModel
 * @property mixed $translatable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attribute query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class, VisibleScope::class])]
final class Attribute extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'attributes';

    protected $fillable = ['name', 'slug', 'type', 'description', 'validation_rules', 'default_value', 'is_required', 'is_filterable', 'is_searchable', 'is_visible', 'is_editable', 'is_sortable', 'sort_order', 'is_enabled', 'is_active', 'category_id', 'group_name', 'icon', 'color', 'min_length', 'max_length', 'min_value', 'max_value', 'step_value', 'placeholder', 'help_text', 'meta_data'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'is_filterable' => 'boolean', 'is_searchable' => 'boolean', 'is_visible' => 'boolean', 'is_editable' => 'boolean', 'is_sortable' => 'boolean', 'is_enabled' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'category_id' => 'integer', 'min_length' => 'integer', 'max_length' => 'integer', 'min_value' => 'float', 'max_value' => 'float', 'step_value' => 'float', 'validation_rules' => 'string', 'meta_data' => 'array'];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['formatted_type', 'validation_rules_array', 'meta_data_array', 'display_name', 'formatted_description', 'type_icon', 'type_color', 'status_badge', 'status_color', 'status_label'];

    protected string $translationModel = \App\Models\Translations\AttributeTranslation::class;

    protected $translatable = ['name'];

    /**
     * Handle values functionality with proper error handling.
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Handle products functionality with proper error handling.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')->withTimestamps();
    }

    /**
     * Handle variants functionality with proper error handling.
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes')->withTimestamps();
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Handle productAttributeValues functionality with proper error handling.
     */
    public function productAttributeValues(): HasManyThrough
    {
        return $this->hasManyThrough(ProductAttributeValue::class, AttributeValue::class, 'attribute_id', 'attribute_value_id', 'id', 'id');
    }

    /**
     * Handle enabledValues functionality with proper error handling.
     */
    public function enabledValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_enabled', true)->orderBy('sort_order');
    }

    /**
     * Handle requiredValues functionality with proper error handling.
     */
    public function requiredValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_required', true)->orderBy('sort_order');
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeFilterable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Handle scopeSearchable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Handle scopeRequired functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeEditable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    /**
     * Handle scopeSortable functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeSortable($query)
    {
        return $query->where('is_sortable', true);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Handle scopeByGroup functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Handle scopeWithValues functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithValues($query)
    {
        return $query->with('values');
    }

    /**
     * Handle scopeWithEnabledValues functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithEnabledValues($query)
    {
        return $query->with('enabledValues');
    }

    // Accessor methods

    /**
     * Handle getFormattedTypeAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getValidationRulesArrayAttribute functionality with proper error handling.
     */
    public function getValidationRulesArrayAttribute(): array
    {
        $rules = $this->validation_rules;
        if (is_array($rules)) {
            return $rules;
        }
        if (is_string($rules)) {
            // Try JSON decode first
            $decoded = json_decode($rules, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Fallback to pipe-delimited
            return array_filter(array_map('trim', explode('|', $rules)));
        }

        return [];
    }

    /**
     * Handle getMetaDataArrayAttribute functionality with proper error handling.
     */
    public function getMetaDataArrayAttribute(): array
    {
        return $this->meta_data ?? [];
    }

    // Mutator methods

    /**
     * Handle setSlugAttribute functionality with proper error handling.
     */
    public function setSlugAttribute(?string $value): void
    {
        $this->attributes['slug'] = $value ?: Str::slug($this->name);
    }

    /**
     * Handle setValidationRulesAttribute functionality with proper error handling.
     *
     * @param  mixed  $value
     */
    public function setValidationRulesAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['validation_rules'] = implode('|', $value);
        } else {
            $this->attributes['validation_rules'] = $value;
        }
    }

    /**
     * Handle setMetaDataAttribute functionality with proper error handling.
     *
     * @param  mixed  $value
     */
    public function setMetaDataAttribute($value): void
    {
        $this->attributes['meta_data'] = is_array($value) ? json_encode($value) : $value;
    }

    // Helper methods

    /**
     * Handle isSelectType functionality with proper error handling.
     */
    public function isSelectType(): bool
    {
        return in_array($this->type, ['select', 'multiselect']);
    }

    /**
     * Handle isNumericType functionality with proper error handling.
     */
    public function isNumericType(): bool
    {
        return in_array($this->type, ['number']);
    }

    /**
     * Handle isTextType functionality with proper error handling.
     */
    public function isTextType(): bool
    {
        return in_array($this->type, ['text', 'textarea']);
    }

    /**
     * Handle isBooleanType functionality with proper error handling.
     */
    public function isBooleanType(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Handle isDateType functionality with proper error handling.
     */
    public function isDateType(): bool
    {
        return $this->type === 'date';
    }

    /**
     * Handle isFileType functionality with proper error handling.
     */
    public function isFileType(): bool
    {
        return in_array($this->type, ['file', 'image']);
    }

    /**
     * Handle getDefaultValueForType functionality with proper error handling.
     */
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

    /**
     * Handle canHaveMultipleValues functionality with proper error handling.
     */
    public function canHaveMultipleValues(): bool
    {
        return in_array($this->type, ['multiselect', 'file', 'image']);
    }

    /**
     * Handle getValuesCount functionality with proper error handling.
     */
    public function getValuesCount(): int
    {
        return $this->values()->count();
    }

    /**
     * Handle getEnabledValuesCount functionality with proper error handling.
     */
    public function getEnabledValuesCount(): int
    {
        return $this->enabledValues()->count();
    }

    // Additional helper methods

    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: $this->slug;
    }

    /**
     * Handle getFormattedDescriptionAttribute functionality with proper error handling.
     */
    public function getFormattedDescriptionAttribute(): string
    {
        return $this->description ? strip_tags($this->description) : '';
    }

    /**
     * Handle getTypeIconAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getTypeColorAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getValidationRulesForForm functionality with proper error handling.
     */
    public function getValidationRulesForForm(): array
    {
        $rules = [];
        if ($this->is_required) {
            $rules[] = 'required';
        }
        $extra = $this->getValidationRulesArrayAttribute();
        if (! empty($extra)) {
            $rules = array_merge($rules, $extra);
        }

        return $rules;
    }

    /**
     * Handle getFormComponentConfig functionality with proper error handling.
     */
    public function getFormComponentConfig(): array
    {
        return ['type' => $this->type, 'label' => $this->name, 'placeholder' => $this->placeholder, 'help_text' => $this->help_text, 'required' => $this->is_required, 'validation_rules' => $this->getValidationRulesForForm(), 'default_value' => $this->default_value, 'min_value' => $this->min_value, 'max_value' => $this->max_value, 'step_value' => $this->step_value, 'options' => $this->isSelectType() ? $this->enabledValues->pluck('value', 'id')->toArray() : []];
    }

    /**
     * Handle isUsedInProducts functionality with proper error handling.
     */
    public function isUsedInProducts(): bool
    {
        return $this->products()->exists();
    }

    /**
     * Handle getUsageCount functionality with proper error handling.
     */
    public function getUsageCount(): int
    {
        return $this->products()->count();
    }

    /**
     * Handle getValuesUsageCount functionality with proper error handling.
     */
    public function getValuesUsageCount(): int
    {
        return $this->values()->whereHas('variants')->count();
    }

    /**
     * Handle getMostUsedValue functionality with proper error handling.
     */
    public function getMostUsedValue()
    {
        return $this->values()->withCount('variants')->orderBy('variants_count', 'desc')->first();
    }

    /**
     * Handle getLeastUsedValue functionality with proper error handling.
     */
    public function getLeastUsedValue()
    {
        return $this->values()->withCount('variants')->orderBy('variants_count', 'asc')->first();
    }

    /**
     * Handle getAverageValuesPerProduct functionality with proper error handling.
     */
    public function getAverageValuesPerProduct(): float
    {
        $totalProducts = $this->getUsageCount();
        if ($totalProducts === 0) {
            return 0;
        }
        $totalValues = $this->values()->whereHas('variants')->count();

        return round($totalValues / $totalProducts, 2);
    }

    /**
     * Handle getPopularityScore functionality with proper error handling.
     */
    public function getPopularityScore(): int
    {
        $usageCount = $this->getUsageCount();
        $valuesCount = $this->getValuesCount();
        $enabledValuesCount = $this->getEnabledValuesCount();
        // Calculate popularity based on usage and values
        $score = $usageCount * 10 + $valuesCount * 2 + $enabledValuesCount * 1;

        return min($score, 100);
        // Cap at 100
    }

    /**
     * Handle getStatusBadgeAttribute functionality with proper error handling.
     */
    public function getStatusBadgeAttribute(): string
    {
        if (! $this->is_enabled) {
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

    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getStatusLabelAttribute functionality with proper error handling.
     */
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

    /**
     * Handle duplicateForGroup functionality with proper error handling.
     */
    public function duplicateForGroup(string $newGroupName): self
    {
        $duplicate = $this->replicate();
        $duplicate->group_name = $newGroupName;
        $duplicate->name = $this->name.' (Copy)';
        $duplicate->slug = $this->slug.'-copy';
        $duplicate->save();
        // Duplicate values
        foreach ($this->values as $value) {
            $valueDuplicate = $value->replicate();
            $valueDuplicate->attribute_id = $duplicate->id;
            $valueDuplicate->save();
        }

        return $duplicate;
    }

    /**
     * Handle mergeWith functionality with proper error handling.
     */
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
                \DB::table('product_attributes')->where('product_id', $product->id)->where('attribute_id', $otherAttribute->id)->where('attribute_value_id', $value->id)->update(['attribute_id' => $this->id]);
            }
        });
        // Delete the other attribute
        $otherAttribute->delete();

        return $this;
    }

    // Advanced Translation Methods

    /**
     * Handle getTranslatedName functionality with proper error handling.
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        $translated = $this->trans('name', $locale);

        return $translated ?: $this->name;
    }

    // Scope for translated attributes

    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Translation Management Methods

    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->unique()->values()->toArray();
    }

    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     *
     * @return App\Models\Translations\AttributeTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\AttributeTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => $this->name]);
    }

    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    /**
     * Handle updateTranslations functionality with proper error handling.
     */
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }

        return true;
    }

    // Helper Methods

    /**
     * Handle getFullDisplayName functionality with proper error handling.
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $name = $this->getTranslatedName($locale);
        $type = $this->getFormattedTypeAttribute();

        return "{$name} ({$type})";
    }

    /**
     * Handle getAttributeInfo functionality with proper error handling.
     */
    public function getAttributeInfo(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'slug' => $this->slug, 'type' => $this->type, 'description' => $this->description, 'group_name' => $this->group_name, 'sort_order' => $this->sort_order, 'is_required' => $this->is_required, 'is_filterable' => $this->is_filterable, 'is_searchable' => $this->is_searchable, 'is_visible' => $this->is_visible, 'is_editable' => $this->is_editable, 'is_sortable' => $this->is_sortable, 'is_enabled' => $this->is_enabled];
    }

    /**
     * Handle getTechnicalInfo functionality with proper error handling.
     */
    public function getTechnicalInfo(): array
    {
        return ['type' => $this->type, 'default_value' => $this->default_value, 'validation_rules' => $this->validation_rules, 'min_value' => $this->min_value, 'max_value' => $this->max_value, 'step_value' => $this->step_value, 'placeholder' => $this->placeholder, 'help_text' => $this->help_text, 'icon' => $this->icon, 'color' => $this->color, 'meta_data' => $this->meta_data];
    }

    /**
     * Handle getBusinessInfo functionality with proper error handling.
     */
    public function getBusinessInfo(): array
    {
        return ['usage_count' => $this->getUsageCount(), 'values_count' => $this->getValuesCount(), 'enabled_values_count' => $this->getEnabledValuesCount(), 'popularity_score' => $this->getPopularityScore(), 'average_values_per_product' => $this->getAverageValuesPerProduct(), 'status' => $this->status_badge, 'status_color' => $this->status_color, 'status_label' => $this->status_label];
    }

    /**
     * Handle getCompleteInfo functionality with proper error handling.
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getAttributeInfo(), $this->getTechnicalInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'formatted_type' => $this->getFormattedTypeAttribute(), 'type_icon' => $this->getTypeIconAttribute(), 'type_color' => $this->getTypeColorAttribute(), 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]);
    }

    /**
     * Handle getStatistics functionality with proper error handling.
     */
    public function getStatistics(): array
    {
        return ['usage_count' => $this->getUsageCount(), 'values_count' => $this->getValuesCount(), 'enabled_values_count' => $this->getEnabledValuesCount(), 'popularity_score' => $this->getPopularityScore(), 'average_values_per_product' => $this->getAverageValuesPerProduct(), 'most_used_value' => $this->getMostUsedValue(), 'least_used_value' => $this->getLeastUsedValue(), 'status' => $this->status_badge, 'status_color' => $this->status_color, 'status_label' => $this->status_label];
    }
}
