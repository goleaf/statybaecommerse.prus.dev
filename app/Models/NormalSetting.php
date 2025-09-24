<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * NormalSetting
 *
 * Eloquent model representing the NormalSetting entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting query()
 *
 * @mixin \Eloquent
 */
final class NormalSetting extends Model
{
    use HasFactory;

    protected $table = 'enhanced_settings';

    protected $fillable = ['group', 'key', 'locale', 'value', 'type', 'description', 'is_public', 'is_encrypted', 'validation_rules', 'sort_order'];

    protected $casts = ['is_public' => 'boolean', 'is_encrypted' => 'boolean', 'sort_order' => 'integer', 'validation_rules' => 'json'];

    /**
     * Handle value functionality with proper error handling.
     */
    protected function value(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if ($this->attributes['is_encrypted'] ?? false) {
                if ($value && $value !== 'null') {
                    try {
                        $decrypted = decrypt($value);
                        if (in_array($this->attributes['type'] ?? '', ['json', 'array']) && is_string($decrypted)) {
                            $decoded = json_decode($decrypted, true);

                            return $decoded !== null ? $decoded : [];
                        }

                        return $decrypted;
                    } catch (\Exception $e) {
                    }
                }
            }
            if (in_array($this->attributes['type'] ?? '', ['json', 'array'])) {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    return $decoded !== null ? $decoded : [];
                }

                return is_array($value) ? $value : [];
            }
            // Handle boolean type
            if (($this->attributes['type'] ?? '') === 'boolean') {
                return (bool) $value;
            }

            return $value;
        }, set: function ($value) {
            if (in_array($this->attributes['type'] ?? '', ['json', 'array']) && (is_array($value) || is_object($value))) {
                $value = json_encode($value);
            }
            if (($this->attributes['is_encrypted'] ?? false) && $value !== null) {
                try {
                    return encrypt($value);
                } catch (\Exception $e) {
                    return $value;
                }
            }

            return $value;
        });
    }

    /**
     * Handle validationRules functionality with proper error handling.
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
     * Handle scopeByGroup functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Handle scopePublic functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('sort_order')->orderBy('key');
    }

    /**
     * Handle getValue functionality with proper error handling.
     *
     * @param  mixed  $default
     */
    public static function getValue(string $key, $default = null, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $setting = self::where('key', $key)->where('locale', $locale)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Handle setValue functionality with proper error handling.
     *
     * @param  mixed  $value
     */
    public static function setValue(string $key, $value, string $group = 'general', ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();
        self::updateOrCreate(['key' => $key, 'locale' => $locale], ['value' => $value, 'group' => $group, 'type' => is_array($value) || is_object($value) ? 'json' : 'text']);
    }

    /**
     * Handle translations functionality with proper error handling.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(NormalSettingTranslation::class);
    }

    /**
     * Handle translation functionality with proper error handling.
     */
    public function translation(?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $translation = $this->translation($locale);

        return $translation?->description ?? $this->description;
    }

    /**
     * Handle getDisplayName functionality with proper error handling.
     */
    public function getDisplayName(?string $locale = null): ?string
    {
        $translation = $this->translation($locale);

        return $translation?->display_name ?? $this->key;
    }

    /**
     * Handle getHelpText functionality with proper error handling.
     */
    public function getHelpText(?string $locale = null): ?string
    {
        $translation = $this->translation($locale);

        return $translation?->help_text;
    }

    /**
     * Handle scopeForLocale functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForLocale($query, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $query->where('locale', $locale);
    }

    /**
     * Handle booted functionality with proper error handling.
     */
    protected static function booted(): void
    {
        self::creating(function (self $setting) {
            if ($setting->is_encrypted && $setting->value !== null) {
                $setting->attributes['value'] = encrypt($setting->value);
            }
        });
        self::updating(function (self $setting) {
            if ($setting->is_encrypted && $setting->isDirty('value') && $setting->value !== null) {
                $setting->attributes['value'] = encrypt($setting->value);
            }
        });
    }
}
