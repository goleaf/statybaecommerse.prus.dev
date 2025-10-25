<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NormalSettingFactory;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\DeadlockException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use JsonSerializable;

/**
 * NormalSetting
 *
 * Eloquent model representing the NormalSetting entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property string|null             $table
 * @property list<string>            $fillable
 * @property array<string, string>   $casts
 * @property string|null             $group
 * @property string                  $key
 * @property string|null             $locale
 * @property mixed                   $value
 * @property string                  $type
 * @property string|null             $description
 * @property bool                    $is_public
 * @property bool                    $is_encrypted
 * @property bool                    $is_active
 * @property array<int, string>|null $validation_rules
 * @property int                     $sort_order
 *
 * @method static NormalSettingFactory                                factory()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NormalSetting query()
 *
 * @use HasFactory<\Database\Factories\NormalSettingFactory>
 *
 * @phpstan-use HasFactory<\Database\Factories\NormalSettingFactory>
 *
 * @mixin \Eloquent
 */
final class NormalSetting extends Model
{
    /** @phpstan-ignore-next-line We rely on Laravel's built-in HasFactory trait despite its unbounded generic signature. */
    use HasFactory;

    /**
     * @var string|null
     */
    protected $table = 'enhanced_settings';

    /**
     * @var list<string> Populates the assignable attributes for mass-assignment.
     */
    protected $fillable = ['group', 'key', 'locale', 'value', 'type', 'description', 'is_public', 'is_encrypted', 'is_active', 'validation_rules', 'sort_order'];

    /**
     * @var array<string, string> Cast map that keeps primitive types predictable when hydrating the model.
     */
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
     * Provide a strongly typed factory hook for static analysis consumers.
     */
    protected static function newFactory(): NormalSettingFactory
    {
        return NormalSettingFactory::new();
    }

    /**
     * Handle value functionality with proper error handling.
     *
     * @return Attribute<mixed, mixed>
     */
    protected function value(): Attribute
    {
        return Attribute::make(get: function ($value) {
            // Quickly short-circuit when the encrypted value is not a string to satisfy strict analysers.
            if (($this->attributes['is_encrypted'] ?? false) && $value !== null) {
                if (! is_string($value)) {
                    return $value;
                }

                try {
                    $decrypted = decrypt($value);
                    if (in_array($this->attributes['type'] ?? '', ['json', 'array']) && is_string($decrypted)) {
                        return safe_json_decode_array($decrypted);
                    }

                    return $decrypted;
                } catch (Exception) {
                }
            }
            if (in_array($this->attributes['type'] ?? '', [self::TYPE_JSON, self::TYPE_ARRAY], true)) {
                return is_string($value) ? safe_json_decode_array($value) : (is_array($value) ? $value : []);
            }
            // Handle boolean type
            if (($this->attributes['type'] ?? '') === self::TYPE_BOOLEAN) {
                return (bool) $value;
            }

            if (($this->attributes['type'] ?? '') === self::TYPE_INTEGER) {
                if ($value === null) {
                    return null;
                }

                return is_numeric($value) ? (int) $value : $value;
            }

            return $value;
        }, set: function ($value) {
            // Safeguard by ensuring the provided value is numeric before casting to int.
            if (($this->attributes['type'] ?? '') === self::TYPE_INTEGER && $value !== null && is_numeric($value)) {
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
                } catch (Exception) {
                    return $value;
                }
            }

            return $value;
        });
    }

    /**
     * Handle validationRules functionality with proper error handling.
     *
     * @return Attribute<array<int, string>, array<int, string>|string|null>
     */
    protected function validationRules(): Attribute
    {
        return Attribute::make(get: function ($value) {
            if ($value === null) {
                return [];
            }
            if (is_string($value)) {
                return safe_json_decode_array($value);
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
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('group')->orderBy('sort_order')->orderBy('key');
    }

    /**
     * Handle getValue functionality with proper error handling.
     */
    public static function getValue(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $query = self::query()->where('key', $key);

        if (self::localeColumnExists()) {
            $locale ??= app()->getLocale();
            $query->where('locale', $locale);
        }

        $setting = $query->first();

        if ($setting !== null) {
            return $setting->value;
        }

        return $default;
    }

    /**
     * Handle setValue functionality with proper error handling.
     */
    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $locale = null): void
    {
        $type = self::inferTypeFromValue($value);
        $supportsLocale = self::localeColumnExists();
        $resolvedLocale = $supportsLocale ? ($locale ?? app()->getLocale()) : null;

        $attributes = ['key' => $key];

        if ($supportsLocale) {
            $attributes['locale'] = $resolvedLocale;
        }

        $values = [
            'group' => $group,
            'type'  => $type,
            'value' => self::normalizeValueForStorage($value, $type),
        ];

        if ($supportsLocale) {
            $values['locale'] = $resolvedLocale;
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                self::updateOrCreate($attributes, $values);

                return;
            } catch (DeadlockException|QueryException $exception) {
                if (! self::isSqliteLockException($exception) || $attempt === 4) {
                    throw $exception;
                }

                usleep(100_000);
            }
        }
    }

    private static function inferTypeFromValue(mixed $value): string
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

    private static function normalizeValueForStorage(mixed $value, string $type): mixed
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
            return $value === null ? null : (is_numeric($value) ? (int) $value : $value);
        }

        if ($type === self::TYPE_BOOLEAN) {
            return $value === null ? null : (bool) $value;
        }

        return $value;
    }

    private static function isSqliteLockException(DeadlockException|QueryException $exception): bool
    {
        if ($exception instanceof DeadlockException) {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'database is locked')
            || str_contains($message, 'database table is locked')
            || (string) $exception->getCode() === '5';
    }

    /**
     * Handle translations functionality with proper error handling.
     */
    /**
     * @return HasMany<NormalSettingTranslation>
     *
     * @phpstan-return HasMany<NormalSettingTranslation, NormalSetting>
     */
    public function translations(): HasMany
    {
        /** @var HasMany<NormalSettingTranslation, NormalSetting> $relation */
        $relation = $this->hasMany(NormalSettingTranslation::class, 'enhanced_setting_id');

        return $relation;
    }

    /**
     * Handle translation functionality with proper error handling.
     */
    public function translation(?string $locale = null): ?NormalSettingTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $translation = $this->translation($locale);

        if ($translation instanceof \App\Models\NormalSettingTranslation) {
            return $translation->description;
        }

        return $this->description;
    }

    /**
     * Handle getDisplayName functionality with proper error handling.
     */
    public function getDisplayName(?string $locale = null): string
    {
        $translation = $this->translation($locale);

        if ($translation instanceof \App\Models\NormalSettingTranslation && $translation->display_name !== null) {
            return $translation->display_name;
        }

        return $this->key;
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
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForLocale(Builder $query, ?string $locale = null): Builder
    {
        if (! self::localeColumnExists()) {
            return $query;
        }

        $locale ??= app()->getLocale();

        return $query->where('locale', $locale);
    }

    /**
     * Handle booted functionality with proper error handling.
     */
    protected static function booted(): void
    {
        self::creating(function (self $setting): void {
            if ($setting->is_encrypted && $setting->value !== null) {
                $setting->attributes['value'] = encrypt($setting->value);
            }
        });
        self::updating(function (self $setting): void {
            if ($setting->is_encrypted && $setting->isDirty('value') && $setting->value !== null) {
                $setting->attributes['value'] = encrypt($setting->value);
            }
        });
    }

    private static function localeColumnExists(): bool
    {
        try {
            return Schema::hasColumn((new self)->getTable(), 'locale');
        } catch (Exception) {
            return false;
        }
    }
}
