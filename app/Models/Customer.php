<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Customer
 *
 * Eloquent model representing the Customer entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 */
#[ScopedBy([ActiveScope::class])]
final class Customer extends Model
{
    use HasFactory, HasTranslations, OrdersByName, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city_id',
        'country_id',
        'postal_code',
        'company_id',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata'  => 'array',
        ];
    }

    /**
     * Handle city relationship with proper error handling.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Handle country relationship with proper error handling.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Handle company relationship with proper error handling.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Associate the customer with a primary customer group when segmentation is in use.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Handle orders relationship with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle addresses relationship with proper error handling.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Handle reviews relationship with proper error handling.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes

    /**
     * Handle scopeActive functionality with proper error handling.
     */
    public function scopeActive(Builder $query): Builder
    {
        // Filter customers that are flagged as active for downstream usage.
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByCity functionality with proper error handling.
     */
    public function scopeByCity(Builder $query, int $cityId): Builder
    {
        // Restrict the query to customers that belong to the provided city identifier.
        return $query->where('city_id', $cityId);
    }

    /**
     * Handle scopeByCountry functionality with proper error handling.
     */
    public function scopeByCountry(Builder $query, int $countryId): Builder
    {
        // Restrict the query to customers that belong to the provided country identifier.
        return $query->where('country_id', $countryId);
    }

    /**
     * Handle scopeByCompany functionality with proper error handling.
     */
    public function scopeByCompany(Builder $query, int $companyId): Builder
    {
        // Restrict the query to customers that belong to the provided company identifier.
        return $query->where('company_id', $companyId);
    }

    // The orderedByName scope is now supplied by the shared OrdersByName trait
    // so that every consumer benefits from the consistent direction guarding.
}
