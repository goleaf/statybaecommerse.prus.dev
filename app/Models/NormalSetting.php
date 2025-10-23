<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JsonSerializable;

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

    protected $fillable = ['group', 'key', 'locale', 'value', 'type', 'description', 'is_public', 'is_encrypted', 'is_active', 'validation_rules', 'sort_order'];

    protected $casts = ['is_public' => 'boolean', 'is_encrypted' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'validation_rules' => 'json'];

    public const TYPE_STRING = 'string';

    public const TYPE_INTEGER = 'integer';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_ARRAY = 'array';

    public const TYPE_JSON = 'json';

    /**
     * @var array<int, string>
     */
    public const CANONICAL_TYPES = [
        self::TYPE_STRING,
        self::TYPE_INTEGER,
        self::TYPE_BOOLEAN,
        self::TYPE_ARRAY,
        self::TYPE_JSON,
    ];

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
                    } catch (Exception $e) {
                    }
                }
            }
            if (in_array($this->attributes['type'] ?? '', [self::TYPE_JSON, self::TYPE_ARRAY], true)) {
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    return $decoded !== null ? $decoded : [];
                }

                return is_array($value) ? $value : [];
            }
            // Handle boolean type
            if (($this->attributes['type'] ?? '') === self::TYPE_BOOLEAN) {
                return (bool) $value;
            }

            if (($this->attributes['type'] ?? '') === self::TYPE_INTEGER) {
                return $value === null ? null : (int) $value;
            }

            return $value;
        }, set: function ($value) {
            if (($this->attributes['type'] ?? '') === self::TYPE_INTEGER && $value !== null) {
                $value = (int) $value;
            }

            if (($this->attributes['type'] ?? '') === self::TYPE_BOOLEAN && $value !== null) {
                $value = (bool) $value;
            }

            if (in_array($this->attributes['type'] ?? '', [self::TYPE_JSON, self::TYPE_ARRAY], true)) {
                if ($value instanceof Arrayable) {
                    $value = $value->toArray();
                } elseif ($value instanceof JsonSerializable) {
                    $value = $value->jsonSerialize();
                } elseif (is_object($value) && method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                }

                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
            }
            if (($this->attributes['is_encrypted'] ?? false) && $value !== null) {
                try {
                    return encrypt($value);
                } catch (Exception $e) {
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
     * @param mixed $query
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Handle scopePublic functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope query to only include active settings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('sort_order')->orderBy('key');
    }

    /**
     * Handle getValue functionality with proper error handling.
     *
     * @param mixed $default
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
     * @param mixed $value
     */
    public static function setValue(string $key, $value, string $group = 'general', ?string $locale = null): void
    {
        $locale = $locale ?? app()->getLocale();
        $type = self::inferTypeFromValue($value);

        self::updateOrCreate(
            ['key' => $key, 'locale' => $locale],
            [
                'group' => $group,
                'type'  => $type,
                'value' => self::normalizeValueForStorage($value, $type),
            ],
        );
    }

    private static function inferTypeFromValue($value): string
    {
        if (is_bool($value)) {
            return self::TYPE_BOOLEAN;
        }

        if (is_int($value)) {
            return self::TYPE_INTEGER;
        }

        if ($value instanceof Arrayable) {
            return self::TYPE_ARRAY;
        }

        if (is_array($value)) {
            return self::TYPE_ARRAY;
        }

        if ($value instanceof JsonSerializable) {
            return self::TYPE_JSON;
        }

        if (is_object($value)) {
            return self::TYPE_JSON;
        }

        return self::TYPE_STRING;
    }

    private static function normalizeValueForStorage($value, string $type)
    {
        if ($type === self::TYPE_ARRAY) {
            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof JsonSerializable) {
                $decoded = $value->jsonSerialize();

                return is_array($decoded) ? $decoded : (array) $decoded;
            }

            if (is_object($value) && method_exists($value, 'toArray')) {
                return $value->toArray();
            }

            return (array) $value;
        }

        if ($type === self::TYPE_JSON) {
            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            }

            if (is_object($value) && method_exists($value, 'toArray')) {
                return $value->toArray();
            }
        }

        if ($type === self::TYPE_INTEGER) {
            return $value === null ? null : (int) $value;
        }

        if ($type === self::TYPE_BOOLEAN) {
            return $value === null ? null : (bool) $value;
        }

        return $value;
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
     * @param mixed $query
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
