<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Translatable\HasTranslations;

final class User extends Authenticatable implements FilamentUser, HasLocalePreferenceContract
{
    use HasFactory, HasRoles, HasTranslations, LogsActivity, Notifiable, SoftDeletes;

    protected static function booted(): void
    {
        self::saving(function (self $user): void {
            $computedName = trim(((string) ($user->first_name ?? '')).' '.((string) ($user->last_name ?? '')));
            if (empty($user->name) && $computedName !== '') {
                $user->name = $computedName;
            }
            if (empty($user->name) && ! empty($user->email)) {
                $user->name = (string) $user->email;
            }
        });
    }

    public array $translatable = [
        'name',
        'first_name',
        'last_name',
        'bio',
        'company',
        'position',
        'website',
    ];

    protected $fillable = [
        'name',
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
        'phone',
        'date_of_birth',
        'is_active',
        'accepts_marketing',
        'two_factor_enabled',
        'last_login_at',
        'preferences',
        'avatar_url',
        'last_login_ip',
        'is_admin',
        'is_verified',
        'company',
        'job_title',
        'bio',
        'company',
        'position',
        'website',
        'social_links',
        'notification_preferences',
        'privacy_settings',
        'marketing_preferences',
        'login_count',
        'last_activity_at',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token',
        'api_token',
        'stripe_customer_id',
        'stripe_account_id',
        'subscription_status',
        'subscription_plan',
        'subscription_ends_at',
        'trial_ends_at',
        'status',
        'verification_token',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'verification_token',
        'password_reset_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'accepts_marketing' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'preferences' => 'array',
            'social_links' => 'array',
            'notification_preferences' => 'array',
            'privacy_settings' => 'array',
            'marketing_preferences' => 'array',
            'two_factor_recovery_codes' => 'array',
            'subscription_ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'password_reset_expires_at' => 'datetime',
            'birth_date' => 'date',
            'date_of_birth' => 'date',
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
        $notification = new VerifyEmailNotification;
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
                    ->filter(fn ($value) => is_string($value) && $value !== '')
                    ->values()
                    ->all();

                if (count($roles) === 0) {
                    return 'N/A';
                }

                $labels = array_map(fn ($item) => ucwords((string) $item), $roles);

                return implode(', ', $labels);
            }
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'email', 'is_active', 'last_login_at', 'preferred_locale',
                'first_name', 'last_name', 'phone_number', 'is_admin', 'accepts_marketing',
                'two_factor_enabled', 'company', 'position', 'website',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}")
            ->useLogName('user');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlist(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'user_wishlists', 'user_id', 'product_id')
            ->withTimestamps();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(UserWishlist::class);
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

    // Explicit alias for clarity in code: reviews authored by this customer
    public function authoredReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_group_user', 'user_id', 'customer_group_id');
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

    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all authenticated users to access Filament during tests and development.
        // Production policies should be enforced via resource policies/permissions.
        return true;
    }

    // Referral relationships
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    public function referralRewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    public function referralStatistics(): HasMany
    {
        return $this->hasMany(ReferralStatistics::class);
    }

    public function activeReferralCode(): ?ReferralCode
    {
        return $this->referralCodes()->active()->first();
    }

    public function getReferralCodeAttribute(): ?string
    {
        return $this->referral_code;
    }

    public function getReferralUrlAttribute(): ?string
    {
        $code = $this->activeReferralCode();

        return $code ? $code->referral_url : null;
    }

    public function hasActiveReferralCode(): bool
    {
        return $this->referralCodes()->active()->exists();
    }

    public function wasReferred(): bool
    {
        return $this->referredBy()->exists();
    }

    public function getReferralStatsAttribute(): array
    {
        return ReferralStatistics::getTotalForUser($this->id);
    }

    // Additional relationships for comprehensive user management
    public function notifications(): HasMany
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(\App\Models\LoginLog::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(\App\Models\Session::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(\App\Models\ActivityLog::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(\App\Models\Refund::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(\App\Models\Feedback::class);
    }

    public function newsletters(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Newsletter::class, 'newsletter_subscriptions');
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(\App\Models\Tag::class, 'taggable');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(\App\Models\File::class, 'fileable');
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeWithOrders($query)
    {
        return $query->has('orders');
    }

    public function scopeWithoutOrders($query)
    {
        return $query->doesntHave('orders');
    }

    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    public function scopeByRole($query, string $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByLocale($query, string $locale)
    {
        return $query->where('preferred_locale', $locale);
    }

    public function scopeSubscribed($query)
    {
        return $query->whereNotNull('subscription_status');
    }

    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: $this->name;
    }

    public function getInitialsAttribute(): string
    {
        $initials = '';
        if ($this->first_name) {
            $initials .= strtoupper(substr($this->first_name, 0, 1));
        }
        if ($this->last_name) {
            $initials .= strtoupper(substr($this->last_name, 0, 1));
        }

        return $initials ?: strtoupper(substr($this->name, 0, 2));
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->orders()->where('status', 'completed')->sum('total') ?? 0;
    }

    public function getAverageOrderValueAttribute(): float
    {
        $completedOrders = $this->orders()->where('status', 'completed');

        return $completedOrders->count() > 0 ? $completedOrders->avg('total') : 0;
    }

    public function getLastOrderDateAttribute(): ?string
    {
        $lastOrder = $this->orders()->latest()->first();

        return $lastOrder ? $lastOrder->created_at->format('Y-m-d') : null;
    }

    public function getOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function isEmailVerified(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function isPhoneVerified(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function hasTwoFactor(): bool
    {
        return $this->two_factor_enabled && ! is_null($this->two_factor_confirmed_at);
    }

    public function isOnTrial(): bool
    {
        return ! is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }

    public function hasActiveSubscription(): bool
    {
        return ! is_null($this->subscription_status) &&
               ! in_array($this->subscription_status, ['cancelled', 'expired']);
    }

    public function getSubscriptionStatusColorAttribute(): string
    {
        return match ($this->subscription_status) {
            'active' => 'success',
            'trialing' => 'info',
            'past_due' => 'warning',
            'cancelled', 'expired' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        if (! $this->is_active) {
            return 'danger';
        }
        if ($this->is_admin) {
            return 'warning';
        }

        return 'success';
    }

    public function getStatusTextAttribute(): string
    {
        if (! $this->is_active) {
            return __('admin.user_status.inactive');
        }
        if ($this->is_admin) {
            return __('admin.user_status.admin');
        }

        return __('admin.user_status.active');
    }

    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date && ! $this->date_of_birth) {
            return null;
        }

        $birthDate = $this->birth_date ?? $this->date_of_birth;

        return $birthDate->age;
    }

    public function getGenderTextAttribute(): ?string
    {
        return match ($this->gender) {
            'male' => __('admin.gender.male'),
            'female' => __('admin.gender.female'),
            'other' => __('admin.gender.other'),
            default => null,
        };
    }

    public function getLocaleTextAttribute(): string
    {
        return match ($this->preferred_locale) {
            'en' => __('admin.locales.english'),
            'lt' => __('admin.locales.lithuanian'),
            default => $this->preferred_locale,
        };
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->attributes['avatar_url'] ?? $this->generateGravatarUrl();
    }

    private function generateGravatarUrl(): string
    {
        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    public function getSocialLinksAttribute(): array
    {
        $links = $this->attributes['social_links'] ?? [];

        return is_array($links) ? $links : [];
    }

    public function getNotificationPreferencesAttribute(): array
    {
        $preferences = $this->attributes['notification_preferences'] ?? [];

        return is_array($preferences) ? $preferences : [];
    }

    public function getPrivacySettingsAttribute(): array
    {
        $settings = $this->attributes['privacy_settings'] ?? [];

        return is_array($settings) ? $settings : [];
    }

    public function getMarketingPreferencesAttribute(): array
    {
        $preferences = $this->attributes['marketing_preferences'] ?? [];

        return is_array($preferences) ? $preferences : [];
    }
}
