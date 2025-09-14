<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Attribute extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_required',
        'is_filterable',
        'is_searchable',
        'is_variant',
        'sort_order',
        'is_enabled',
        'options',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_variant' => 'boolean',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'options' => 'array',
    ];

    protected string $translationModel = \App\Models\Translations\AttributeTranslation::class;
    protected array $translatable = ['name', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'type', 'is_enabled'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function enabledValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_enabled', true);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForVariants($query)
    {
        return $query->where('is_variant', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isSelectType(): bool
    {
        return $this->type === 'select';
    }

    public function isTextType(): bool
    {
        return $this->type === 'text';
    }

    public function isNumberType(): bool
    {
        return $this->type === 'number';
    }

    public function isBooleanType(): bool
    {
        return $this->type === 'boolean';
    }

    public function isColorType(): bool
    {
        return $this->type === 'color';
    }

    public function getOptionsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = $value ? json_encode($value) : null;
    }
}