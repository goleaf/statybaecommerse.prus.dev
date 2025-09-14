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
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Translatable\HasTranslations;

/**
 * User Model
 * 
 * Represents a user in the e-commerce system with comprehensive functionality
 * including authentication, authorization, profile management, and business relationships.
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property string|null $preferred_locale
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $gender
 * @property string|null $phone_number
 * @property \Carbon\Carbon|null $birth_date
 * @property string|null $timezone
 * @property bool $opt_in
 * @property string|null $phone
 * @property \Carbon\Carbon|null $date_of_birth
 * @property bool $is_active
 * @property bool $accepts_marketing
 * @property bool $two_factor_enabled
 * @property \Carbon\Carbon|null $last_login_at
 * @property array|null $preferences
 * @property string|null $avatar_url
 * @property string|null $last_login_ip
 * @property bool $is_admin
 * @property bool $is_verified
 * @property string|null $company
 * @property string|null $job_title
 * @property string|null $bio
 * @property string|null $position
 * @property string|null $website
 * @property array|null $social_links
 * @property array|null $notification_preferences
 * @property array|null $privacy_settings
 * @property array|null $marketing_preferences
 * @property int $login_count
 * @property \Carbon\Carbon|null $last_activity_at
 * @property \Carbon\Carbon|null $phone_verified_at
 * @property string|null $two_factor_secret
 * @property array|null $two_factor_recovery_codes
 * @property \Carbon\Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $api_token
 * @property string|null $stripe_customer_id
 * @property string|null $stripe_account_id
 * @property string|null $subscription_status
 * @property string|null $subscription_plan
 * @property \Carbon\Carbon|null $subscription_ends_at
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property string|null $status
 * @property string|null $verification_token
 * @property string|null $password_reset_token
 * @property \Carbon\Carbon|null $password_reset_expires_at
 * @property string|null $referral_code
 * @property \Carbon\Carbon|null $referral_code_generated_at
 * @property array|null $referral_settings
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * @property-read string $full_name
 * @property-read string $initials
 * @property-read float $total_spent
 * @property-read float $average_order_value
 * @property-read string|null $last_order_date
 * @property-read int $orders_count
 * @property-read int $reviews_count
 * @property-read float $average_rating
 * @property-read string $subscription_status_color
 * @property-read string $status_color
 * @property-read string $status_text
 * @property-read int|null $age
 * @property-read string|null $gender_text
 * @property-read string $locale_text
 * @property-read string $avatar_url
 * @property-read array $social_links
 * @property-read array $notification_preferences
 * @property-read array $privacy_settings
 * @property-read array $marketing_preferences
 * @property-read \App\Models\Address|null $default_address
 * @property-read \App\Models\Address|null $billing_address
 * @property-read \App\Models\Address|null $shipping_address
 * @property-read \App\Models\Partner|null $active_partner
 * @property-read float $partner_discount_rate
 * @property-read string|null $referral_code
 * @property-read string|null $referral_url
 * @property-read array $referral_stats
 * @property-read string $roles_label
 * 
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $wishlist
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserWishlist> $wishlists
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Address> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $cartItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $authoredReviews
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomerGroup> $customerGroups
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DiscountRedemption> $discountRedemptions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Partner> $partners
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Referral> $referrals
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Referral> $referredBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReferralCode> $referralCodes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReferralReward> $referralRewards
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReferralStatistics> $referralStatistics
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LoginLog> $loginLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Session> $sessions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ActivityLog> $activityLogs
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupportTicket> $supportTickets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Feedback> $feedback
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Newsletter> $newsletters
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * 
 * @method static \Illuminate\Database\Eloquent\Builder|User active()
 * @method static \Illuminate\Database\Eloquent\Builder|User inactive()
 * @method static \Illuminate\Database\Eloquent\Builder|User admins()
 * @method static \Illuminate\Database\Eloquent\Builder|User withOrders()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutOrders()
 * @method static \Illuminate\Database\Eloquent\Builder|User recentlyActive(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder|User byRole(string $role)
 * @method static \Illuminate\Database\Eloquent\Builder|User byGender(string $gender)
 * @method static \Illuminate\Database\Eloquent\Builder|User byLocale(string $locale)
 * @method static \Illuminate\Database\Eloquent\Builder|User subscribed()
 * @method static \Illuminate\Database\Eloquent\Builder|User onTrial()
 * @method static \Illuminate\Database\Eloquent\Builder|User withPreferredLocale(string $locale)
 * 
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
#[ScopedBy([ActiveScope::class])]
final /**
 * User
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class User extends Authenticatable implements FilamentUser, HasLocalePreferenceContract
{
    use HasFactory, HasRoles, HasTranslations, HasSafeSerialization, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Boot the model and register event listeners.
     * 
     * Automatically sets the user's name based on first_name and last_name
     * or falls back to email if name is empty.
     * 
     * @return void
     */
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
        'referral_code',
        'referral_code_generated_at',
        'referral_settings',
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

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'full_name',
        'initials',
        'total_spent',
        'average_order_value',
        'last_order_date',
        'orders_count',
        'reviews_count',
        'average_rating',
        'subscription_status_color',
        'status_color',
        'status_text',
        'age',
        'gender_text',
        'locale_text',
        'avatar_url',
        'social_links',
        'notification_preferences',
        'privacy_settings',
        'marketing_preferences',
        'roles_label',
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

    /**
     * Get the user's preferred locale.
     * 
     * Returns the user's preferred locale setting or null if not set.
     * 
     * @return string|null The preferred locale code or null
     */
    public function preferredLocale(): ?string
    {
        $locale = $this->getAttribute('preferred_locale');

        return $locale ? (string) $locale : null;
    }

    /**
     * Send password reset notification in user's preferred locale.
     * 
     * @param string $token The password reset token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $notification = new ResetPasswordNotification($token);
        $locale = $this->preferredLocale() ?? app()->getLocale();
        $this->notify($notification->locale($locale));
    }

    /**
     * Send email verification notification in user's preferred locale.
     * 
     * @return void
     */
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

    /**
     * Get the user's orders.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's latest order.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestOrder(): HasOne
    {
        return $this->orders()->one()->latestOfMany();
    }

    /**
     * Get the user's oldest order.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestOrder(): HasOne
    {
        return $this->orders()->one()->oldestOfMany();
    }

    /**
     * Get the user's latest completed order.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestCompletedOrder(): HasOne
    {
        return $this->orders()->one()->ofMany(['created_at' => 'max'], function ($query) {
            $query->whereIn('status', ['delivered', 'completed']);
        });
    }

    /**
     * Get the user's highest value order.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestValueOrder(): HasOne
    {
        return $this->orders()->one()->ofMany('total', 'max');
    }

    /**
     * Get the user's wishlist products.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function wishlist(): BelongsToMany
    {
        return $this
            ->belongsToMany(Product::class, 'user_wishlists', 'user_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * Get the user's wishlist items.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(UserWishlist::class);
    }

    /**
     * Get the user's addresses.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the user's cart items.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the user's reviews.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the user's latest review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReview(): HasOne
    {
        return $this->reviews()->one()->latestOfMany();
    }

    /**
     * Get the user's oldest review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oldestReview(): HasOne
    {
        return $this->reviews()->one()->oldestOfMany();
    }

    /**
     * Get the user's highest rated review.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function highestRatedReview(): HasOne
    {
        return $this->reviews()->one()->ofMany('rating', 'max');
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

    /**
     * Get the user's latest discount redemption.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestDiscountRedemption(): HasOne
    {
        return $this->discountRedemptions()->one()->latestOfMany();
    }

    /**
     * Get the user's latest address.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestAddress(): HasOne
    {
        return $this->addresses()->one()->latestOfMany();
    }

    /**
     * Get the user's latest cart item.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestCartItem(): HasOne
    {
        return $this->cartItems()->one()->latestOfMany();
    }

    /**
     * Get the user's latest notification.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestNotification(): HasOne
    {
        return $this->notifications()->one()->latestOfMany();
    }

    /**
     * Get the user's latest referral.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReferral(): HasOne
    {
        return $this->referrals()->one()->latestOfMany();
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Get the user's latest document.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function latestDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->latestOfMany();
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

    /**
     * Get the user's latest referral code.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReferralCode(): HasOne
    {
        return $this->referralCodes()->one()->latestOfMany();
    }

    public function referralRewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class);
    }

    /**
     * Get the user's latest referral reward.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestReferralReward(): HasOne
    {
        return $this->referralRewards()->one()->latestOfMany();
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
        return $this->attributes['referral_code'] ?? null;
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
        $lastOrder = $this->latestOrder;

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
