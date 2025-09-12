<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class SystemSetting extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'category_id',
        'key',
        'name',
        'value',
        'type',
        'group',
        'description',
        'help_text',
        'is_public',
        'is_required',
        'is_encrypted',
        'is_readonly',
        'validation_rules',
        'options',
        'default_value',
        'sort_order',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_required' => 'boolean',
        'is_encrypted' => 'boolean',
        'is_readonly' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'json',
        'options' => 'json',
        'sort_order' => 'integer',
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_encrypted && $value) {
                    try {
                        $value = decrypt($value);
                    } catch (\Exception $e) {
                        // If decryption fails, return the original value
                    }
                }

                return match ($this->type) {
                    'boolean' => (bool) json_decode($value ?? 'false'),
                    'integer' => (int) ($value ?? 0),
                    'float' => (float) ($value ?? 0.0),
                    'array', 'json' => json_decode($value ?? '[]', true) ?? [],
                    'file' => $this->getFirstMediaUrl('files'),
                    'image' => $this->getFirstMediaUrl('images'),
                    default => $value,
                };
            },
            set: function ($value) {
                if ($this->is_encrypted && $value) {
                    $value = encrypt($value);
                }

                return match ($this->type) {
                    'boolean' => json_encode((bool) $value),
                    'integer' => (string) (int) $value,
                    'float' => (string) (float) $value,
                    'array', 'json' => json_encode($value),
                    default => (string) $value,
                };
            }
        );
    }

    protected function options(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null) {
                    return [];
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return $decoded !== null ? $decoded : [];
                }
                return $value;
            },
            set: function ($value) {
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value;
            }
        );
    }

    protected function validationRules(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($value === null) {
                    return [];
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return $decoded !== null ? $decoded : [];
                }
                return $value;
            },
            set: function ($value) {
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value;
            }
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'name', 'value', 'type', 'group', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "System Setting {$eventName}")
            ->useLogName('system_settings');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class, 'category_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SystemSettingTranslation::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(SystemSettingHistory::class);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(SystemSettingDependency::class, 'setting_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(SystemSettingDependency::class, 'depends_on_setting_id');
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->whereHas('category', function ($q) use ($category) {
            $q->where('slug', $category);
        });
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearchable($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('key', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->active()->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, array $options = []): void
    {
        $defaults = [
            'type' => 'string',
            'group' => 'general',
            'is_public' => false,
            'is_required' => false,
            'is_encrypted' => false,
            'is_readonly' => false,
            'is_active' => true,
        ];

        $data = array_merge($defaults, $options, [
            'key' => $key,
            'value' => $value,
            'updated_by' => auth()->id(),
        ]);

        static::updateOrCreate(
            ['key' => $key],
            $data
        );
    }

    public static function getPublic(string $key, $default = null)
    {
        $setting = static::where('key', $key)->public()->active()->first();
        return $setting ? $setting->value : $default;
    }

    public function getTranslatedName(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->name ?? $this->name;
    }

    public function getTranslatedDescription(string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->description ?? $this->description;
    }

    public function getTranslatedHelpText(string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->help_text ?? $this->help_text;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this->addMediaCollection('files')
            ->acceptsMimeTypes(['application/pdf', 'text/plain', 'application/json'])
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->performOnCollections('images');

        $this->addMediaConversion('small')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->performOnCollections('images');
    }

    public function getValidationRulesArray(): array
    {
        $rules = $this->validation_rules;
        
        if ($this->is_required) {
            $rules['required'] = true;
        }

        return $rules;
    }

    public function getOptionsArray(): array
    {
        return $this->options;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function isGroup(string $group): bool
    {
        return $this->group === $group;
    }

    public function canBeModified(): bool
    {
        return !$this->is_readonly;
    }

    public function getFormattedValue(): string
    {
        return match ($this->type) {
            'boolean' => $this->value ? __('admin.yes') : __('admin.no'),
            'array', 'json' => json_encode($this->value, JSON_PRETTY_PRINT),
            'file', 'image' => $this->value ? basename($this->value) : __('admin.not_set'),
            default => (string) $this->value,
        };
    }

    public function getDisplayValue(): string
    {
        if ($this->is_encrypted) {
            return '[ENCRYPTED]';
        }

        return match ($this->type) {
            'boolean' => $this->value ? __('admin.yes') : __('admin.no'),
            'array', 'json' => json_encode($this->value, JSON_PRETTY_PRINT),
            'file', 'image' => $this->value ? basename($this->value) : __('admin.not_set'),
            'color' => $this->value ? '<span style="background-color: ' . $this->value . '; width: 20px; height: 20px; display: inline-block; border-radius: 3px;"></span> ' . $this->value : __('admin.not_set'),
            default => (string) $this->value,
        };
    }

    public function getIconForType(): string
    {
        return match ($this->type) {
            'string', 'text' => 'heroicon-o-document-text',
            'number' => 'heroicon-o-calculator',
            'boolean' => 'heroicon-o-check-circle',
            'array', 'json' => 'heroicon-o-code-bracket',
            'file' => 'heroicon-o-document',
            'image' => 'heroicon-o-photo',
            'select' => 'heroicon-o-list-bullet',
            'color' => 'heroicon-o-swatch',
            'date', 'datetime' => 'heroicon-o-calendar-days',
            default => 'heroicon-o-cog-6-tooth',
        };
    }

    public function getColorForType(): string
    {
        return match ($this->type) {
            'string', 'text' => 'gray',
            'number' => 'blue',
            'boolean' => 'green',
            'array', 'json' => 'purple',
            'file', 'image' => 'orange',
            'select' => 'indigo',
            'color' => 'pink',
            'date', 'datetime' => 'yellow',
            default => 'gray',
        };
    }

    public function getBadgeForStatus(): string
    {
        $badges = [];
        
        if ($this->is_public) {
            $badges[] = __('admin.system_settings.public');
        }
        
        if ($this->is_encrypted) {
            $badges[] = __('admin.system_settings.encrypted');
        }
        
        if ($this->is_required) {
            $badges[] = __('admin.system_settings.required');
        }
        
        if ($this->is_readonly) {
            $badges[] = __('admin.system_settings.readonly');
        }
        
        return implode(', ', $badges);
    }

    public function hasDependencies(): bool
    {
        return $this->dependencies()->active()->exists();
    }

    public function hasDependents(): bool
    {
        return $this->dependents()->exists();
    }

    public function getDependencyChain(): array
    {
        $chain = [];
        $visited = [];
        
        $this->buildDependencyChain($this, $chain, $visited);
        
        return $chain;
    }

    private function buildDependencyChain(SystemSetting $setting, array &$chain, array &$visited): void
    {
        if (in_array($setting->id, $visited)) {
            return; // Prevent circular dependencies
        }
        
        $visited[] = $setting->id;
        $chain[] = $setting;
        
        foreach ($setting->dependencies as $dependency) {
            $this->buildDependencyChain($dependency->dependsOn, $chain, $visited);
        }
    }

    public function validateValue($value): bool
    {
        $rules = $this->getValidationRulesArray();
        
        if (empty($rules)) {
            return true;
        }
        
        $validator = validator([$this->key => $value], [$this->key => $rules]);
        
        return !$validator->fails();
    }

    public function getValidationErrors($value): array
    {
        $rules = $this->getValidationRulesArray();
        
        if (empty($rules)) {
            return [];
        }
        
        $validator = validator([$this->key => $value], [$this->key => $rules]);
        
        return $validator->fails() ? $validator->errors()->all() : [];
    }

    public function getCacheKey(): string
    {
        return 'system_setting_' . $this->key;
    }

    public function getCacheTags(): array
    {
        return ['system_settings', 'system_setting_' . $this->id, 'group_' . $this->group];
    }

    public static function clearCache(): void
    {
        cache()->tags(['system_settings'])->flush();
    }

    public function clearCache(): void
    {
        cache()->tags($this->getCacheTags())->forget($this->getCacheKey());
    }

    public function getApiResponse(): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'value' => $this->is_public ? $this->value : null,
            'type' => $this->type,
            'group' => $this->group,
            'description' => $this->description,
            'help_text' => $this->help_text,
            'is_public' => $this->is_public,
            'is_required' => $this->is_required,
            'is_readonly' => $this->is_readonly,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'updated_at' => $this->updated_at,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        
        static::creating(function (SystemSetting $setting) {
            $setting->updated_by = auth()->id();
        });
        
        static::updating(function (SystemSetting $setting) {
            $setting->updated_by = auth()->id();
            $setting->clearCache();
        });
        
        static::deleting(function (SystemSetting $setting) {
            $setting->clearCache();
        });
    }

    public function getActiveDependencies()
    {
        return $this->dependencies()->active()->with('dependsOnSetting')->get();
    }

    public function getActiveDependents()
    {
        return $this->dependents()->active()->with('setting')->get();
    }

    public function canBeEnabled(): bool
    {
        $dependencies = $this->getActiveDependencies();
        
        foreach ($dependencies as $dependency) {
            if (!$dependency->isConditionMet()) {
                return false;
            }
        }
        
        return true;
    }

    public function getDependencyStatus(): array
    {
        $dependencies = $this->getActiveDependencies();
        $status = [
            'can_enable' => true,
            'blocking_dependencies' => [],
            'met_dependencies' => [],
        ];

        foreach ($dependencies as $dependency) {
            if ($dependency->isConditionMet()) {
                $status['met_dependencies'][] = $dependency;
            } else {
                $status['blocking_dependencies'][] = $dependency;
                $status['can_enable'] = false;
            }
        }

        return $status;
    }

    public function addToHistory(string $oldValue = null, string $newValue = null, string $reason = null): void
    {
        $this->history()->create([
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => auth()->id(),
            'change_reason' => $reason,
        ]);
    }

    public function getRecentHistory(int $limit = 10)
    {
        return $this->history()
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTranslatedValue(string $locale = null): mixed
    {
        // For now, return the regular value
        // In the future, this could be extended to support translated values
        return $this->value;
    }

    public function getValidationRulesForForm(): array
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }

        $validationRules = $this->getValidationRulesArray();
        foreach ($validationRules as $rule => $value) {
            if (is_bool($value) && $value) {
                $rules[] = $rule;
            } elseif (!is_bool($value)) {
                $rules[] = "{$rule}:{$value}";
            }
        }

        return $rules;
    }

    public function getFormFieldConfig(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->getTranslatedName(),
            'help_text' => $this->getTranslatedHelpText(),
            'required' => $this->is_required,
            'readonly' => $this->is_readonly,
            'options' => $this->getOptionsArray(),
            'validation_rules' => $this->getValidationRulesForForm(),
            'default_value' => $this->default_value,
        ];
    }
}
