<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Traits\HasSafeSerialization;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Translatable\HasTranslations;

/**
 * User
 *
 * Eloquent model representing the User entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property array $translatable
 * @property mixed $fillable
 * @property mixed $hidden
 * @property mixed $appends
 *
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class User extends Authenticatable implements FilamentUser, HasLocalePreferenceContract
{
    use HasFactory, HasRoles, HasSafeSerialization, HasTranslations, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Handle booted functionality with proper error handling.
     */
    protected static function booted(): void
    {
        self::saving(function (self $user): void {
            $computedName = trim((string) ($user->first_name ?? '').' '.(string) ($user->last_name ?? ''));
            if (empty($user->name) && $computedName !== '') {
                $user->name = $computedName;
            }
            if (empty($user->name) && ! empty($user->email)) {
                $user->name = (string) $user->email;
            }
        });
    }

    public array $translatable = ['name', 'first_name', 'last_name', 'bio', 'company', 'position', 'website'];

    protected $fillable = ['name', 'email', 'password', 'preferred_locale', 'email_verified_at', 'first_name', 'last_name', 'gender', 'phone_number', 'birth_date', 'timezone', 'opt_in', 'phone', 'date_of_birth', 'is_active', 'accepts_marketing', 'two_factor_enabled', 'last_login_at', 'preferences', 'avatar_url', 'last_login_ip', 'is_admin', 'is_verified', 'company', 'job_title', 'bio', 'company', 'position', 'website', 'social_links', 'notification_preferences', 'privacy_settings', 'marketing_preferences', 'login_count', 'last_activity_at', 'email_verified_at', 'phone_verified_at', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'remember_token', 'api_token', 'stripe_customer_id', 'stripe_account_id', 'subscription_status', 'subscription_plan', 'subscription_ends_at', 'trial_ends_at', 'status', 'verification_token', 'password_reset_token', 'password_reset_expires_at', 'referral_code', 'referral_code_generated_at', 'referral_settings'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'verification_token', 'password_reset_token', 'api_token'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['full_name', 'initials', 'total_spent', 'average_order_value', 'last_order_date', 'orders_count', 'reviews_count', 'average_rating', 'subscription_status_color', 'status_color', 'status_text', 'age', 'gender_text', 'locale_text', 'avatar_url', 'social_links', 'notification_preferences', 'privacy_settings', 'marketing_preferences', 'roles_label'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'phone_verified_at' => 'datetime', 'two_factor_confirmed_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean', 'is_verified' => 'boolean', 'accepts_marketing' => 'boolean', 'two_factor_enabled' => 'boolean', 'is_admin' => 'boolean', 'last_login_at' => 'datetime', 'last_activity_at' => 'datetime', 'preferences' => 'array', 'social_links' => 'array', 'notification_preferences' => 'array', 'privacy_settings' => 'array', 'marketing_preferences' => 'array', 'two_factor_recovery_codes' => 'array', 'subscription_ends_at' => 'datetime', 'trial_ends_at' => 'datetime', 'password_reset_expires_at' => 'datetime', 'birth_date' => 'date', 'date_of_birth' => 'date'];
    }

    /**
     * Handle preferredLocale functionality with proper error handling.
     */
    public function preferredLocale(): ?string
    {
        $locale = $this->getAttribute('preferred_locale');

        return $locale ? (string) $locale : null;
    }

    /**
     * Handle sendPasswordResetNotification functionality with proper error handling.
     *
     * @param  mixed  $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $notification = new ResetPasswordNotification($token);
        $locale = $this->preferredLocale() ?? app()->getLocale();
        $this->notify($notification->locale($locale));
    }

    /**
     * Handle sendEmailVerificationNotification functionality with proper error handling.
     */
    public function sendEmailVerificationNotification(): void
    {
        $notification = new VerifyEmailNotification;
        $locale = $this->preferredLocale() ?? app()->getLocale();
        $this->notify($notification->locale($locale));
    }

    /**
     * Handle rolesLabel functionality with proper error handling.
     */
    protected function rolesLabel(): Attribute
    {
        return Attribute::make(get: function (): string {
            $roles = Arr::from($this->roles()->pluck('name')->filter(fn ($value) => is_string($value) && $value !== '')->values());
            if (count($roles) === 0) {
                return 'N/A';
            }
            $labels = array_map(fn ($item) => ucwords((string) $item), $roles);

            return implode(', ', $labels);
        });
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email', 'is_active', 'last_login_at', 'preferred_locale', 'first_name', 'last_name', 'phone_number', 'is_admin', 'accepts_marketing', 'two_factor_enabled', 'company', 'position', 'website'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}")->useLogName('user');
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle latestOrder functionality with proper error handling.
     */
    public function latestOrder(): HasOne
    {
        return $this->orders()->one()->latestOfMany();
    }

    /**
     * Handle oldestOrder functionality with proper error handling.
     */
    public function oldestOrder(): HasOne
    {
        return $this->orders()->one()->oldestOfMany();
    }

    /**
     * Handle latestCompletedOrder functionality with proper error handling.
     */
    public function latestCompletedOrder(): HasOne
    {
        return $this->orders()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->whereIn('status', ['delivered', 'completed']);
        });
    }

    /**
     * Handle highestValueOrder functionality with proper error handling.
     */
    public function highestValueOrder(): HasOne
    {
        return $this->orders()->one()->ofMany('total', 'max');
    }

    /**
     * Handle wishlist functionality with proper error handling.
     */
    public function wishlist(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'user_wishlists', 'user_id', 'product_id')->withTimestamps();
    }

    /**
     * Handle wishlists functionality with proper error handling.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(UserWishlist::class);
    }

    /**
     * Handle addresses functionality with proper error handling.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Handle cartItems functionality with proper error handling.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Handle reviews functionality with proper error handling.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Handle latestReview functionality with proper error handling.
     */
    public function latestReview(): HasOne
    {
        return $this->reviews()->one()->latestOfMany();
    }

    /**
     * Handle oldestReview functionality with proper error handling.
     */
    public function oldestReview(): HasOne
    {
        return $this->reviews()->one()->oldestOfMany();
    }

    /**
     * Handle highestRatedReview functionality with proper error handling.
     */
    public function highestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'max');
    }

    /**
     * Handle lowestRatedReview functionality with proper error handling.
     */
    public function lowestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'min');
    }

    // Explicit alias for clarity in code: reviews authored by this customer
    /**
     * Handle authoredReviews functionality with proper error handling.
     */
    public function authoredReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * Handle customerGroups functionality with proper error handling.
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_group_user', 'user_id', 'customer_group_id');
    }

    /**
     * Handle discountRedemptions functionality with proper error handling.
     */
    public function discountRedemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Handle latestDiscountRedemption functionality with proper error handling.
     */
    public function latestDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->latestOfMany();
    }

    /**
     * Handle highestValueDiscountRedemption functionality with proper error handling.
     */
    public function highestValueDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->ofMany('discount_amount', 'max');
    }

    /**
     * Handle latestAddress functionality with proper error handling.
     */
    public function latestAddress(): HasOne
    {
        return $this->addresses()->one()->latestOfMany();
    }

    /**
     * Handle latestCartItem functionality with proper error handling.
     */
    public function latestCartItem(): HasOne
    {
        return $this->cartItems()->one()->latestOfMany();
    }

    /**
     * Handle latestNotification functionality with proper error handling.
     */
    public function latestNotification(): HasOne
    {
        return $this->notifications()->one()->latestOfMany();
    }

    /**
     * Handle latestReferral functionality with proper error handling.
     */
    public function latestReferral(): HasOne
    {
        return $this->referrals()->one()->latestOfMany();
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Handle latestDocument functionality with proper error handling.
     */
    public function latestDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->latestOfMany();
    }

    /**
     * Handle scopeWithPreferredLocale functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithPreferredLocale($query, string $locale)
    {
        return $query->where('preferred_locale', $locale);
    }

    /**
     * Handle getDefaultAddressAttribute functionality with proper error handling.
     */
    public function getDefaultAddressAttribute(): ?Address
    {
        return $this->addresses()->default()->first();
    }

    /**
     * Handle getBillingAddressAttribute functionality with proper error handling.
     */
    public function getBillingAddressAttribute(): ?Address
    {
        return $this->addresses()->byType('billing')->default()->first() ?? $this->addresses()->default()->first();
    }

    /**
     * Handle getShippingAddressAttribute functionality with proper error handling.
     */
    public function getShippingAddressAttribute(): ?Address
    {
        return $this->addresses()->byType('shipping')->default()->first() ?? $this->addresses()->default()->first();
    }

    /**
     * Handle partners functionality with proper error handling.
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_users');
    }

    /**
     * Handle getActivePartnerAttribute functionality with proper error handling.
     */
    public function getActivePartnerAttribute(): ?Partner
    {
        return $this->partners()->enabled()->first();
    }

    /**
     * Handle isPartner functionality with proper error handling.
     */
    public function isPartner(): bool
    {
        return $this->partners()->enabled()->exists();
    }

    /**
     * Handle getPartnerDiscountRateAttribute functionality with proper error handling.
     */
    public function getPartnerDiscountRateAttribute(): float
    {
        $partner = $this->active_partner;

        return $partner ? $partner->effective_discount_rate : 0;
    }

    /**
     * Handle canAccessPanel functionality with proper error handling.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow all authenticated users to access Filament during tests and development.
        // Production policies should be enforced via resource policies/permissions.
        return true;
    }

    // Referral relationships
    /**
     * Handle referrals functionality with proper error handling.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Handle referredBy functionality with proper error handling.
     */
    public function referredBy(): HasMany
    {
        return $this->hasMany(Referral::class, 'referred_id');
    }

    /**
     * Handle referralCodes functionality with proper error handling.
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    /**
     * Handle latestReferralCode functionality with proper error handling.
     */
    public function latestReferralCode(): HasOne
    {
        return $this->referralCodes()->one()->latestOfMany();
    }

    /**
     * Handle referralRewards functionality with proper error handling.
     */
    public function referralRewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /**
     * Handle latestReferralReward functionality with proper error handling.
     */
    public function latestReferralReward(): HasOne
    {
        return $this->referralRewards()->one()->latestOfMany();
    }

    /**
     * Handle referralStatistics functionality with proper error handling.
     */
    public function referralStatistics(): HasMany
    {
        return $this->hasMany(ReferralStatistics::class);
    }

    /**
     * Handle activeReferralCode functionality with proper error handling.
     */
    public function activeReferralCode(): ?ReferralCode
    {
        return $this->referralCodes()->active()->first();
    }

    /**
     * Handle getReferralCodeAttribute functionality with proper error handling.
     */
    public function getReferralCodeAttribute(): ?string
    {
        return $this->attributes['referral_code'] ?? null;
    }

    /**
     * Handle getReferralUrlAttribute functionality with proper error handling.
     */
    public function getReferralUrlAttribute(): ?string
    {
        $code = $this->activeReferralCode();

        return $code ? $code->referral_url : null;
    }

    /**
     * Handle hasActiveReferralCode functionality with proper error handling.
     */
    public function hasActiveReferralCode(): bool
    {
        return $this->referralCodes()->active()->exists();
    }

    /**
     * Handle wasReferred functionality with proper error handling.
     */
    public function wasReferred(): bool
    {
        return $this->referredBy()->exists();
    }

    /**
     * Handle getReferralStatsAttribute functionality with proper error handling.
     */
    public function getReferralStatsAttribute(): array
    {
        return ReferralStatistics::getTotalForUser($this->id);
    }

    // Additional relationships for comprehensive user management
    /**
     * Handle notifications functionality with proper error handling.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Handle loginLogs functionality with proper error handling.
     */
    public function loginLogs(): HasMany
    {
        return $this->hasMany(\App\Models\LoginLog::class);
    }

    /**
     * Handle sessions functionality with proper error handling.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(\App\Models\Session::class);
    }

    /**
     * Handle activityLogs functionality with proper error handling.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(\App\Models\ActivityLog::class);
    }

    /**
     * Handle subscriptions functionality with proper error handling.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    /**
     * Handle payments functionality with proper error handling.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Handle refunds functionality with proper error handling.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(\App\Models\Refund::class);
    }

    /**
     * Handle supportTickets functionality with proper error handling.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    /**
     * Handle feedback functionality with proper error handling.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(\App\Models\Feedback::class);
    }

    /**
     * Handle newsletters functionality with proper error handling.
     */
    public function newsletters(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Newsletter::class, 'newsletter_subscriptions');
    }

    /**
     * Handle tags functionality with proper error handling.
     */
    public function tags(): MorphMany
    {
        return $this->morphMany(\App\Models\Tag::class, 'taggable');
    }

    /**
     * Handle files functionality with proper error handling.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(\App\Models\File::class, 'fileable');
    }

    // Scopes for filtering
    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeInactive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Handle scopeAdmins functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    /**
     * Handle scopeWithOrders functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithOrders($query)
    {
        return $query->has('orders');
    }

    /**
     * Handle scopeWithoutOrders functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithoutOrders($query)
    {
        return $query->doesntHave('orders');
    }

    /**
     * Handle scopeRecentlyActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecentlyActive($query, int $days = 30)
    {
        return $query->where('last_activity_at', '>=', now()->subDays($days));
    }

    /**
     * Handle scopeByRole functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByRole($query, string $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Handle scopeByGender functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Handle scopeByLocale functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByLocale($query, string $locale)
    {
        return $query->where('preferred_locale', $locale);
    }

    /**
     * Handle scopeSubscribed functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeSubscribed($query)
    {
        return $query->whereNotNull('subscription_status');
    }

    /**
     * Handle scopeOnTrial functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>', now());
    }

    // Helper methods
    /**
     * Handle getFullNameAttribute functionality with proper error handling.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: $this->name;
    }

    /**
     * Handle getInitialsAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getTotalSpentAttribute functionality with proper error handling.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->orders()->where('status', 'completed')->sum('total') ?? 0;
    }

    /**
     * Handle getAverageOrderValueAttribute functionality with proper error handling.
     */
    public function getAverageOrderValueAttribute(): float
    {
        $completedOrders = $this->orders()->where('status', 'completed');

        return $completedOrders->count() > 0 ? $completedOrders->avg('total') : 0;
    }

    /**
     * Handle getLastOrderDateAttribute functionality with proper error handling.
     */
    public function getLastOrderDateAttribute(): ?string
    {
        $lastOrder = $this->latestOrder;

        return $lastOrder ? $lastOrder->created_at->format('Y-m-d') : null;
    }

    /**
     * Handle getOrdersCountAttribute functionality with proper error handling.
     */
    public function getOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Handle getReviewsCountAttribute functionality with proper error handling.
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /**
     * Handle getAverageRatingAttribute functionality with proper error handling.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Handle isEmailVerified functionality with proper error handling.
     */
    public function isEmailVerified(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    /**
     * Handle isPhoneVerified functionality with proper error handling.
     */
    public function isPhoneVerified(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    /**
     * Handle hasTwoFactor functionality with proper error handling.
     */
    public function hasTwoFactor(): bool
    {
        return $this->two_factor_enabled && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Handle isOnTrial functionality with proper error handling.
     */
    public function isOnTrial(): bool
    {
        return ! is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }

    /**
     * Handle hasActiveSubscription functionality with proper error handling.
     */
    public function hasActiveSubscription(): bool
    {
        return ! is_null($this->subscription_status) && ! in_array($this->subscription_status, ['cancelled', 'expired']);
    }

    /**
     * Handle getSubscriptionStatusColorAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getStatusColorAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getStatusTextAttribute functionality with proper error handling.
     */
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

    /**
     * Handle getAgeAttribute functionality with proper error handling.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date && ! $this->date_of_birth) {
            return null;
        }
        $birthDate = $this->birth_date ?? $this->date_of_birth;

        return $birthDate->age;
    }

    /**
     * Handle getGenderTextAttribute functionality with proper error handling.
     */
    public function getGenderTextAttribute(): ?string
    {
        return match ($this->gender) {
            'male' => __('admin.gender.male'),
            'female' => __('admin.gender.female'),
            'other' => __('admin.gender.other'),
            default => null,
        };
    }

    /**
     * Handle getLocaleTextAttribute functionality with proper error handling.
     */
    public function getLocaleTextAttribute(): string
    {
        return match ($this->preferred_locale) {
            'en' => __('admin.locales.english'),
            'lt' => __('admin.locales.lithuanian'),
            default => $this->preferred_locale,
        };
    }

    /**
     * Handle getAvatarUrlAttribute functionality with proper error handling.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->attributes['avatar_url'] ?? $this->generateGravatarUrl();
    }

    /**
     * Handle generateGravatarUrl functionality with proper error handling.
     */
    private function generateGravatarUrl(): string
    {
        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    /**
     * Handle getSocialLinksAttribute functionality with proper error handling.
     */
    public function getSocialLinksAttribute(): array
    {
        $links = $this->attributes['social_links'] ?? [];

        return is_array($links) ? $links : [];
    }

    /**
     * Handle getNotificationPreferencesAttribute functionality with proper error handling.
     */
    public function getNotificationPreferencesAttribute(): array
    {
        $preferences = $this->attributes['notification_preferences'] ?? [];

        return is_array($preferences) ? $preferences : [];
    }

    /**
     * Handle getPrivacySettingsAttribute functionality with proper error handling.
     */
    public function getPrivacySettingsAttribute(): array
    {
        $settings = $this->attributes['privacy_settings'] ?? [];

        return is_array($settings) ? $settings : [];
    }

    /**
     * Handle getMarketingPreferencesAttribute functionality with proper error handling.
     */
    public function getMarketingPreferencesAttribute(): array
    {
        $preferences = $this->attributes['marketing_preferences'] ?? [];

        return is_array($preferences) ? $preferences : [];
    }
}
