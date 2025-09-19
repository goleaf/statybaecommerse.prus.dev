<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * SystemSetting
 *
 * Eloquent model representing the SystemSetting entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class SystemSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = ['category_id', 'key', 'name', 'value', 'type', 'group', 'description', 'help_text', 'is_public', 'is_required', 'is_encrypted', 'is_readonly', 'validation_rules', 'options', 'default_value', 'sort_order', 'is_active', 'updated_by', 'placeholder', 'tooltip', 'metadata', 'validation_message', 'is_cacheable', 'cache_ttl', 'cache_key', 'environment', 'tags', 'version', 'access_count', 'last_accessed_at'];
    protected $casts = ['is_public' => 'boolean', 'is_required' => 'boolean', 'is_encrypted' => 'boolean', 'is_readonly' => 'boolean', 'is_active' => 'boolean', 'is_cacheable' => 'boolean', 'validation_rules' => 'json', 'options' => 'json', 'metadata' => 'json', 'tags' => 'json', 'sort_order' => 'integer', 'cache_ttl' => 'integer', 'access_count' => 'integer', 'last_accessed_at' => 'datetime'];

    /**
     * Handle value functionality with proper error handling.
     * @return Attribute
     */
    protected function value(): Attribute
    {
        return Attribute::make(get: function ($value) {
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
        }, set: function ($value) {
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
        });
    }

    /**
     * Handle options functionality with proper error handling.
     * @return Attribute
     */
    protected function options(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if ($value === null) {
                return [];
            }
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return $decoded !== null ? $decoded : [];
            }
            return $value;
        }, set: function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return $value;
        });
    }

    /**
     * Handle validationRules functionality with proper error handling.
     * @return Attribute
     */
    protected function validationRules(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if ($value === null) {
                return [];
            }
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return $decoded !== null ? $decoded : [];
            }
            return $value;
        }, set: function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_encode($value);
            }
            return $value;
        });
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['key', 'name', 'value', 'type', 'group', 'is_active'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "System Setting {$eventName}")->useLogName('system_settings');
    }

    /**
     * Handle category functionality with proper error handling.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class, 'category_id');
    }

    /**
     * Handle updatedBy functionality with proper error handling.
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Handle translations functionality with proper error handling.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SystemSettingTranslation::class);
    }

    /**
     * Handle history functionality with proper error handling.
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(SystemSettingHistory::class);
    }

    /**
     * Handle dependencies functionality with proper error handling.
     * @return HasMany
     */
    public function dependencies(): HasMany
    {
        return $this->hasMany(SystemSettingDependency::class, 'setting_id');
    }

    /**
     * Handle dependents functionality with proper error handling.
     * @return HasMany
     */
    public function dependents(): HasMany
    {
        return $this->hasMany(SystemSettingDependency::class, 'depends_on_setting_id');
    }

    /**
     * Handle scopeByGroup functionality with proper error handling.
     * @param mixed $query
     * @param string $group
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     * @param mixed $query
     * @param string $category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->whereHas('category', function ($q) use ($category) {
            $q->where('slug', $category);
        });
    }

    /**
     * Handle scopePublic functionality with proper error handling.
     * @param mixed $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Handle scopeSearchable functionality with proper error handling.
     * @param mixed $query
     * @param string $search
     */
    public function scopeSearchable($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('key', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Handle getValue functionality with proper error handling.
     * @param string $key
     * @param mixed $default
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->active()->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Handle setValue functionality with proper error handling.
     * @param string $key
     * @param mixed $value
     * @param array $options
     * @return void
     */
    public static function setValue(string $key, $value, array $options = []): void
    {
        $defaults = ['type' => 'string', 'group' => 'general', 'is_public' => false, 'is_required' => false, 'is_encrypted' => false, 'is_readonly' => false, 'is_active' => true];
        $data = array_merge($defaults, $options, ['key' => $key, 'value' => $value, 'updated_by' => auth()->id()]);
        self::updateOrCreate(['key' => $key], $data);
    }

    /**
     * Handle getPublic functionality with proper error handling.
     * @param string $key
     * @param mixed $default
     */
    public static function getPublic(string $key, $default = null)
    {
        $setting = self::where('key', $key)->public()->active()->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->name ?? $this->name;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->description ?? $this->description;
    }

    /**
     * Handle getTranslatedHelpText functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedHelpText(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->help_text ?? $this->help_text;
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('files')->acceptsMimeTypes(['application/pdf', 'text/plain', 'application/json'])->singleFile();
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(150)->height(150)->sharpen(10)->performOnCollections('images');
        $this->addMediaConversion('small')->width(300)->height(300)->sharpen(10)->performOnCollections('images');
    }

    /**
     * Handle getValidationRulesArray functionality with proper error handling.
     * @return array
     */
    public function getValidationRulesArray(): array
    {
        $rules = $this->validation_rules;
        if ($this->is_required) {
            $rules['required'] = true;
        }
        return $rules;
    }

    /**
     * Handle getOptionsArray functionality with proper error handling.
     * @return array
     */
    public function getOptionsArray(): array
    {
        return $this->options;
    }

    /**
     * Handle isType functionality with proper error handling.
     * @param string $type
     * @return bool
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Handle isGroup functionality with proper error handling.
     * @param string $group
     * @return bool
     */
    public function isGroup(string $group): bool
    {
        return $this->group === $group;
    }

    /**
     * Handle canBeModified functionality with proper error handling.
     * @return bool
     */
    public function canBeModified(): bool
    {
        return !$this->is_readonly;
    }

    /**
     * Handle getFormattedValue functionality with proper error handling.
     * @return string
     */
    public function getFormattedValue(): string
    {
        return match ($this->type) {
            'boolean' => $this->value ? __('admin.yes') : __('admin.no'),
            'array', 'json' => json_encode($this->value, JSON_PRETTY_PRINT),
            'file', 'image' => $this->value ? basename($this->value) : __('admin.not_set'),
            default => (string) $this->value,
        };
    }

    /**
     * Handle getDisplayValue functionality with proper error handling.
     * @return string
     */
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

    /**
     * Handle getIconForType functionality with proper error handling.
     * @return string
     */
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

    /**
     * Handle getColorForType functionality with proper error handling.
     * @return string
     */
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

    /**
     * Handle getBadgeForStatus functionality with proper error handling.
     * @return string
     */
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

    /**
     * Handle hasDependencies functionality with proper error handling.
     * @return bool
     */
    public function hasDependencies(): bool
    {
        return $this->dependencies()->active()->exists();
    }

    /**
     * Handle hasDependents functionality with proper error handling.
     * @return bool
     */
    public function hasDependents(): bool
    {
        return $this->dependents()->exists();
    }

    /**
     * Handle getDependencyChain functionality with proper error handling.
     * @return array
     */
    public function getDependencyChain(): array
    {
        $chain = [];
        $visited = [];
        $this->buildDependencyChain($this, $chain, $visited);
        return $chain;
    }

    /**
     * Handle buildDependencyChain functionality with proper error handling.
     * @param SystemSetting $setting
     * @param array $chain
     * @param array $visited
     * @return void
     */
    private function buildDependencyChain(SystemSetting $setting, array &$chain, array &$visited): void
    {
        if (in_array($setting->id, $visited)) {
            return;
            // Prevent circular dependencies
        }
        $visited[] = $setting->id;
        $chain[] = $setting;
        foreach ($setting->dependencies as $dependency) {
            $this->buildDependencyChain($dependency->dependsOn, $chain, $visited);
        }
    }

    /**
     * Handle validateValue functionality with proper error handling.
     * @param mixed $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        $rules = $this->getValidationRulesArray();
        if (empty($rules)) {
            return true;
        }
        $validator = validator([$this->key => $value], [$this->key => $rules]);
        return !$validator->fails();
    }

    /**
     * Handle getValidationErrors functionality with proper error handling.
     * @param mixed $value
     * @return array
     */
    public function getValidationErrors($value): array
    {
        $rules = $this->getValidationRulesArray();
        if (empty($rules)) {
            return [];
        }
        $validator = validator([$this->key => $value], [$this->key => $rules]);
        return $validator->fails() ? $validator->errors()->all() : [];
    }

    /**
     * Handle getCacheKey functionality with proper error handling.
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'system_setting_' . $this->key;
    }

    /**
     * Handle getCacheTags functionality with proper error handling.
     * @return array
     */
    public function getCacheTags(): array
    {
        return ['system_settings', 'system_setting_' . $this->id, 'group_' . $this->group];
    }

    /**
     * Handle clearCache functionality with proper error handling.
     * @return void
     */
    public static function clearCache(): void
    {
        cache()->tags(['system_settings'])->flush();
    }

    /**
     * Handle clearInstanceCache functionality with proper error handling.
     * @return void
     */
    public function clearInstanceCache(): void
    {
        cache()->tags($this->getCacheTags())->forget($this->getCacheKey());
    }

    /**
     * Handle getApiResponse functionality with proper error handling.
     * @return array
     */
    public function getApiResponse(): array
    {
        return ['id' => $this->id, 'key' => $this->key, 'name' => $this->name, 'value' => $this->is_public ? $this->value : null, 'type' => $this->type, 'group' => $this->group, 'description' => $this->description, 'help_text' => $this->help_text, 'is_public' => $this->is_public, 'is_required' => $this->is_required, 'is_readonly' => $this->is_readonly, 'is_active' => $this->is_active, 'sort_order' => $this->sort_order, 'updated_at' => $this->updated_at];
    }

    /**
     * Boot the service provider or trait functionality.
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function (SystemSetting $setting) {
            $setting->updated_by = auth()->id();
        });
        self::updating(function (SystemSetting $setting) {
            $setting->updated_by = auth()->id();
            $setting->clearCache();
        });
        self::deleting(function (SystemSetting $setting) {
            $setting->clearCache();
        });
    }

    /**
     * Handle getActiveDependencies functionality with proper error handling.
     */
    public function getActiveDependencies()
    {
        return $this->dependencies()->active()->with('dependsOnSetting')->get();
    }

    /**
     * Handle getActiveDependents functionality with proper error handling.
     */
    public function getActiveDependents()
    {
        return $this->dependents()->active()->with('setting')->get();
    }

    /**
     * Handle canBeEnabled functionality with proper error handling.
     * @return bool
     */
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

    /**
     * Handle getDependencyStatus functionality with proper error handling.
     * @return array
     */
    public function getDependencyStatus(): array
    {
        $dependencies = $this->getActiveDependencies();
        $status = ['can_enable' => true, 'blocking_dependencies' => [], 'met_dependencies' => []];
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

    /**
     * Handle addToHistory functionality with proper error handling.
     * @param string|null $oldValue
     * @param string|null $newValue
     * @param string|null $reason
     * @return void
     */
    public function addToHistory(?string $oldValue = null, ?string $newValue = null, ?string $reason = null): void
    {
        $this->history()->create(['old_value' => $oldValue, 'new_value' => $newValue, 'changed_by' => auth()->id(), 'change_reason' => $reason]);
    }

    /**
     * Handle getRecentHistory functionality with proper error handling.
     * @param int $limit
     */
    public function getRecentHistory(int $limit = 10)
    {
        return $this->history()->with('changedBy')->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    /**
     * Handle getTranslatedValue functionality with proper error handling.
     * @param string|null $locale
     * @return mixed
     */
    public function getTranslatedValue(?string $locale = null): mixed
    {
        // For now, return the regular value
        // In the future, this could be extended to support translated values
        return $this->value;
    }

    /**
     * Handle getValidationRulesForForm functionality with proper error handling.
     * @return array
     */
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

    /**
     * Handle getFormFieldConfig functionality with proper error handling.
     * @return array
     */
    public function getFormFieldConfig(): array
    {
        return ['type' => $this->type, 'label' => $this->getTranslatedName(), 'help_text' => $this->getTranslatedHelpText(), 'required' => $this->is_required, 'readonly' => $this->is_readonly, 'options' => $this->getOptionsArray(), 'validation_rules' => $this->getValidationRulesForForm(), 'default_value' => $this->default_value];
    }
}
