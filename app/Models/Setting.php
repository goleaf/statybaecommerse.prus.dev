<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * Setting
 * 
 * Eloquent model representing the Setting entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'value', 'type', 'group', 'description', 'display_name', 'is_public', 'is_required', 'is_encrypted'];
    protected $casts = ['is_public' => 'boolean', 'is_required' => 'boolean', 'is_encrypted' => 'boolean'];
    /**
     * Handle getValueAttribute functionality with proper error handling.
     * @param mixed $value
     */
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
    /**
     * Handle setValueAttribute functionality with proper error handling.
     * @param mixed $value
     * @return void
     */
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
    /**
     * Handle get functionality with proper error handling.
     * @param string $key
     * @param mixed $default
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    /**
     * Handle set functionality with proper error handling.
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @param string|null $description
     * @return void
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type, 'description' => $description]);
    }
    /**
     * Handle getPublic functionality with proper error handling.
     * @param string $key
     * @param mixed $default
     */
    public static function getPublic(string $key, $default = null)
    {
        $setting = self::where('key', $key)->where('is_public', true)->first();
        return $setting ? $setting->value : $default;
    }
}