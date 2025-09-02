<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

final class EnhancedSetting extends Model
{
    use HasFactory;

    protected $table = 'enhanced_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'is_encrypted',
        'validation_rules',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'json',
        'validation_rules' => 'json',
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected function value(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->is_encrypted && $value) {
                    return decrypt($value);
                }
                return $value;
            },
            set: function ($value) {
                if ($this->is_encrypted && $value) {
                    return encrypt($value);
                }
                return $value;
            }
        );
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group')->orderBy('sort_order')->orderBy('key');
    }

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }
}