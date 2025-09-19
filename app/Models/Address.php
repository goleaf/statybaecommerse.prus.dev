<?php

declare (strict_types=1);
namespace App\Models;

use App\Enums\AddressType;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
/**
 * Address
 * 
 * Eloquent model representing the Address entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $translatable
 * @property mixed $appends
 * @method static \Illuminate\Database\Eloquent\Builder|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Address query()
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class Address extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;
    protected $table = 'addresses';
    protected $fillable = ['user_id', 'type', 'first_name', 'last_name', 'company', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country_code', 'phone', 'email', 'is_default', 'is_billing', 'is_shipping', 'notes', 'apartment', 'floor', 'building', 'landmark', 'instructions', 'company_name', 'company_vat', 'is_active'];
    protected $translatable = ['notes', 'instructions'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_billing' => 'boolean', 'is_shipping' => 'boolean', 'is_active' => 'boolean', 'type' => AddressType::class];
    }
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name', 'full_address', 'formatted_address', 'display_name', 'type_label', 'type_icon', 'type_color'];
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle country functionality with proper error handling.
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'cca2')->withDefault(['name' => 'Unknown Country', 'cca2' => 'XX']);
    }
    /**
     * Handle countryById functionality with proper error handling.
     * @return BelongsTo
     */
    public function countryById(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    /**
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle cityById functionality with proper error handling.
     * @return BelongsTo
     */
    public function cityById(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }
    /**
     * Handle shippingOrders functionality with proper error handling.
     * @return HasMany
     */
    public function shippingOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }
    /**
     * Handle latestOrder functionality with proper error handling.
     * @return HasOne
     */
    public function latestOrder(): HasOne
    {
        return $this->orders()->one()->latestOfMany();
    }
    /**
     * Handle latestShippingOrder functionality with proper error handling.
     * @return HasOne
     */
    public function latestShippingOrder(): HasOne
    {
        return $this->shippingOrders()->one()->latestOfMany();
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeBilling functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeBilling($query)
    {
        return $query->where('is_billing', true);
    }
    /**
     * Handle scopeShipping functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeShipping($query)
    {
        return $query->where('is_shipping', true);
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
     * Handle scopeForUser functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeByCountry functionality with proper error handling.
     * @param mixed $query
     * @param string $countryCode
     */
    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }
    /**
     * Handle scopeByCity functionality with proper error handling.
     * @param mixed $query
     * @param string $city
     */
    public function scopeByCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }
    /**
     * Handle scopeByPostalCode functionality with proper error handling.
     * @param mixed $query
     * @param string $postalCode
     */
    public function scopeByPostalCode($query, string $postalCode)
    {
        return $query->where('postal_code', 'like', "%{$postalCode}%");
    }
    /**
     * Handle getFullNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
    /**
     * Handle getFullAddressAttribute functionality with proper error handling.
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address_line_1, $this->address_line_2, $this->apartment, $this->floor, $this->building, $this->city, $this->state, $this->postal_code]);
        return implode(', ', $parts);
    }
    /**
     * Handle getFormattedAddressAttribute functionality with proper error handling.
     * @return string
     */
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
    /**
     * Handle getDisplayNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        if ($this->company_name) {
            $name .= " ({$this->company_name})";
        }
        return $name;
    }
    /**
     * Handle getTypeLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }
    /**
     * Handle getTypeIconAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeIconAttribute(): string
    {
        return $this->type->icon();
    }
    /**
     * Handle getTypeColorAttribute functionality with proper error handling.
     * @return string
     */
    public function getTypeColorAttribute(): string
    {
        return $this->type->color();
    }
    /**
     * Handle isBilling functionality with proper error handling.
     * @return bool
     */
    public function isBilling(): bool
    {
        return $this->is_billing || $this->type === AddressType::BILLING;
    }
    /**
     * Handle isShipping functionality with proper error handling.
     * @return bool
     */
    public function isShipping(): bool
    {
        return $this->is_shipping || $this->type === AddressType::SHIPPING;
    }
    /**
     * Handle isDefault functionality with proper error handling.
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default;
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
    /**
     * Handle hasCompany functionality with proper error handling.
     * @return bool
     */
    public function hasCompany(): bool
    {
        return !empty($this->company_name);
    }
    /**
     * Handle hasAdditionalInfo functionality with proper error handling.
     * @return bool
     */
    public function hasAdditionalInfo(): bool
    {
        return !empty($this->apartment) || !empty($this->floor) || !empty($this->building) || !empty($this->landmark) || !empty($this->instructions);
    }
    /**
     * Handle getValidationRules functionality with proper error handling.
     * @return array
     */
    public function getValidationRules(): array
    {
        return ['user_id' => 'required|exists:users,id', 'type' => 'required|in:' . implode(',', AddressType::values()), 'first_name' => 'required|string|max:255', 'last_name' => 'required|string|max:255', 'company_name' => 'nullable|string|max:255', 'company_vat' => 'nullable|string|max:50', 'address_line_1' => 'required|string|max:255', 'address_line_2' => 'nullable|string|max:255', 'apartment' => 'nullable|string|max:100', 'floor' => 'nullable|string|max:100', 'building' => 'nullable|string|max:100', 'city' => 'required|string|max:100', 'state' => 'nullable|string|max:100', 'postal_code' => 'required|string|max:20', 'country_code' => 'required|string|size:2', 'country_id' => 'nullable|exists:countries,id', 'zone_id' => 'nullable|exists:zones,id', 'city_id' => 'nullable|exists:cities,id', 'phone' => 'nullable|string|max:20', 'email' => 'nullable|email|max:255', 'is_default' => 'boolean', 'is_billing' => 'boolean', 'is_shipping' => 'boolean', 'is_active' => 'boolean', 'notes' => 'nullable|string|max:1000', 'instructions' => 'nullable|string|max:1000', 'landmark' => 'nullable|string|max:255'];
    }
    /**
     * Handle getTypesForSelect functionality with proper error handling.
     * @return array
     */
    public static function getTypesForSelect(): array
    {
        return AddressType::options();
    }
    /**
     * Handle getTypesWithDescriptions functionality with proper error handling.
     * @return array
     */
    public static function getTypesWithDescriptions(): array
    {
        return AddressType::optionsWithDescriptions();
    }
    /**
     * Handle getDefaultAddressForUser functionality with proper error handling.
     * @param int $userId
     * @return self|null
     */
    public static function getDefaultAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->where('is_default', true)->where('is_active', true)->first();
    }
    /**
     * Handle getBillingAddressForUser functionality with proper error handling.
     * @param int $userId
     * @return self|null
     */
    public static function getBillingAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->where(function ($query) {
            $query->where('is_billing', true)->orWhere('type', AddressType::BILLING);
        })->where('is_active', true)->first();
    }
    /**
     * Handle getShippingAddressForUser functionality with proper error handling.
     * @param int $userId
     * @return self|null
     */
    public static function getShippingAddressForUser(int $userId): ?self
    {
        return static::where('user_id', $userId)->where('is_active', true)->where(function ($query) {
            $query->where('is_shipping', true)->orWhere(function ($subQuery) {
                $subQuery->where('is_shipping', false)->where('type', AddressType::SHIPPING);
            });
        })->orderBy('is_shipping', 'desc')->first();
    }
    /**
     * Handle getAddressesForUser functionality with proper error handling.
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getAddressesForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)->where('is_active', true)->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get();
    }
    /**
     * Handle setAsDefault functionality with proper error handling.
     * @return bool
     */
    public function setAsDefault(): bool
    {
        // Remove default from other addresses
        static::where('user_id', $this->user_id)->where('id', '!=', $this->id)->update(['is_default' => false]);
        // Set this address as default
        return $this->update(['is_default' => true]);
    }
    /**
     * Handle duplicateForUser functionality with proper error handling.
     * @param int $userId
     * @return self
     */
    public function duplicateForUser(int $userId): self
    {
        $newAddress = $this->replicate();
        $newAddress->user_id = $userId;
        $newAddress->is_default = false;
        $newAddress->save();
        return $newAddress;
    }
}