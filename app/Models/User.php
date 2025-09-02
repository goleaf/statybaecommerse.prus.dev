<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasLocalePreferenceContract
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'email',
        'password',
        'preferred_locale',
        'email_verified_at',
        'first_name',
        'last_name',
        'gender',
        'phone_number',
        'birth_date',
        'timezone',
        'opt_in',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function preferredLocale(): ?string
    {
        $locale = $this->getAttribute('preferred_locale');
        return $locale ? (string) $locale : null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $notification = new ResetPasswordNotification($token);
        $locale = $this->preferredLocale() ?? app()->getLocale();
        $this->notify($notification->locale($locale));
    }

    public function sendEmailVerificationNotification(): void
    {
        $notification = new VerifyEmailNotification();
        $locale = $this->preferredLocale() ?? app()->getLocale();
        $this->notify($notification->locale($locale));
    }

    protected function rolesLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $roles = $this
                    ->roles()
                    ->pluck('name')
                    ->filter(fn($value) => is_string($value) && $value !== '')
                    ->values()
                    ->all();

                if (count($roles) === 0) {
                    return 'N/A';
                }

                $labels = array_map(fn($item) => ucwords((string) $item), $roles);
                return implode(', ', $labels);
            }
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_group_user', 'user_id', 'group_id');
    }

    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function scopeWithPreferredLocale($query, string $locale)
    {
        return $query->where('preferred_locale', $locale);
    }

    public function getDefaultAddressAttribute(): ?Address
    {
        return $this->addresses()->default()->first();
    }

    public function getBillingAddressAttribute(): ?Address
    {
        return $this->addresses()->byType('billing')->default()->first()
            ?? $this->addresses()->default()->first();
    }

    public function getShippingAddressAttribute(): ?Address
    {
        return $this->addresses()->byType('shipping')->default()->first()
            ?? $this->addresses()->default()->first();
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_users');
    }

    public function getActivePartnerAttribute(): ?Partner
    {
        return $this->partners()->enabled()->first();
    }

    public function isPartner(): bool
    {
        return $this->partners()->enabled()->exists();
    }

    public function getPartnerDiscountRateAttribute(): float
    {
        $partner = $this->active_partner;
        return $partner ? $partner->effective_discount_rate : 0;
    }
}
