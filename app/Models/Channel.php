<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Channel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'channels';

    protected $fillable = [
        'name',
        'slug',
        'code',
        'type',
        'description',
        'timezone',
        'url',
        'is_enabled',
        'is_default',
        'is_active',
        'sort_order',
        'metadata',
        'configuration',
        'domain',
        'ssl_enabled',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'analytics_tracking_id',
        'analytics_enabled',
        'payment_methods',
        'default_payment_method',
        'shipping_methods',
        'default_shipping_method',
        'free_shipping_threshold',
        'currency_code',
        'currency_symbol',
        'currency_position',
        'default_language',
        'supported_languages',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_media',
        'legal_documents',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'ssl_enabled' => 'boolean',
            'analytics_enabled' => 'boolean',
            'sort_order' => 'integer',
            'free_shipping_threshold' => 'decimal:2',
            'metadata' => 'array',
            'configuration' => 'array',
            'payment_methods' => 'array',
            'shipping_methods' => 'array',
            'supported_languages' => 'array',
            'social_media' => 'array',
            'legal_documents' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
