<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

final class Address extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'addresses';

    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'country_id',
        'zone_id',
        'region_id',
        'city_id',
        'phone',
        'email',
        'is_default',
        'is_billing',
        'is_shipping',
        'notes',
        'apartment',
        'floor',
        'building',
        'landmark',
        'instructions',
        'company_name',
        'company_vat',
        'is_active',
    ];

    protected $translatable = [
        'notes',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
            'is_active' => 'boolean',
            'type' => AddressType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'cca2');
    }

    public function countryById(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function cityById(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'billing_address_id');
    }

    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBilling($query)
    {
        return $query->where('is_billing', true);
    }

    public function scopeShipping($query)
    {
        return $query->where('is_shipping', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', 'like', "%{$postalCode}%");
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->apartment,
            $this->floor,
            $this->building,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    public function getFormattedAddressAttribute(): string
    {
        $lines = [];
        
        if ($this->company_name) {
            $lines[] = $this->company_name;
        }
        
        $lines[] = $this->full_name;
        $lines[] = $this->address_line_1;
        
        if ($this->address_line_2) {
            $lines[] = $this->address_line_2;
        }
        
        if ($this->apartment) {
            $lines[] = $this->apartment;
        }
        
        if ($this->floor) {
            $lines[] = $this->floor;
        }
        
        if ($this->building) {
            $lines[] = $this->building;
        }
        
        $cityStateZip = array_filter([$this->city, $this->state, $this->postal_code]);
        if (!empty($cityStateZip)) {
            $lines[] = implode(', ', $cityStateZip);
        }
        
        if ($this->country) {
            $lines[] = $this->country->name;
        }
        
        return implode("\n", $lines);
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        
        if ($this->company_name) {
            $name .= " ({$this->company_name})";
        }
        
        return $name;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }

    public function getTypeIconAttribute(): string
    {
        return $this->type->icon();
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type->color();
    }

    public function isBilling(): bool
    {
        return $this->is_billing || $this->type === AddressType::BILLING;
    }

    public function isShipping(): bool
    {
        return $this->is_shipping || $this->type === AddressType::SHIPPING;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function hasCompany(): bool
    {
        return !empty($this->company_name);
    }

    public function hasAdditionalInfo(): bool
    {
        return !empty($this->apartment) || 
               !empty($this->floor) || 
               !empty($this->building) || 
               !empty($this->landmark) || 
               !empty($this->instructions);
    }

    public function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:' . implode(',', AddressType::values()),
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_vat' => 'nullable|string|max:50',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'country_id' => 'nullable|exists:countries,id',
            'zone_id' => 'nullable|exists:zones,id',
            'region_id' => 'nullable|exists:regions,id',
            'city_id' => 'nullable|exists:cities,id',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_default' => 'boolean',
            'is_billing' => 'boolean',
            'is_shipping' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'instructions' => 'nullable|string|max:1000',
            'landmark' => 'nullable|string|max:255',
        ];
    }

    public static function getTypesForSelect(): array
    {
        return AddressType::options();
    }

    public static function getTypesWithDescriptions(): array
    {
        return AddressType::optionsWithDescriptions();
    }

    public static function getDefaultAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function getBillingAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where(function ($query) {
                $query->where('is_billing', true)
                      ->orWhere('type', AddressType::BILLING);
            })
            ->where('is_active', true)
            ->first();
    }

    public static function getShippingAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_shipping', true)
                      ->orWhere(function ($subQuery) {
                          $subQuery->where('is_shipping', false)
                                   ->where('type', AddressType::SHIPPING);
                      });
            })
            ->orderBy('is_shipping', 'desc') // Prioritize is_shipping = true
            ->first();
    }

    public static function getAddressesForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function setAsDefault(): bool
    {
        // Remove default from other addresses
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this address as default
        return $this->update(['is_default' => true]);
    }

    public function duplicateForUser(int $userId): self
    {
        $newAddress = $this->replicate();
        $newAddress->user_id = $userId;
        $newAddress->is_default = false;
        $newAddress->save();

        return $newAddress;
    }
}
