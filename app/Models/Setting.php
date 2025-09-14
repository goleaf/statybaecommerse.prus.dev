<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final /**
 * Setting
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'display_name',
        'is_public',
        'is_required',
        'is_encrypted',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_required' => 'boolean',
        'is_encrypted' => 'boolean',
    ];

    public function getValueAttribute($value)
    {
        if ($this->type === 'boolean') {
            return (bool) json_decode($value);
        }

        if ($this->type === 'number') {
            return is_numeric($value) ? (float) $value : 0;
        }

        if (in_array($this->type, ['json', 'array'])) {
            return json_decode($value, true);
        }

        return $value;
    }

    public function setValueAttribute($value): void
    {
        if ($this->type === 'boolean') {
            $this->attributes['value'] = json_encode((bool) $value);
        } elseif ($this->type === 'number') {
            $this->attributes['value'] = (string) $value;
        } elseif (in_array($this->type, ['json', 'array'])) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = (string) $value;
        }
    }

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    public static function getPublic(string $key, $default = null)
    {
        $setting = self::where('key', $key)->where('is_public', true)->first();

        return $setting ? $setting->value : $default;
    }
}
