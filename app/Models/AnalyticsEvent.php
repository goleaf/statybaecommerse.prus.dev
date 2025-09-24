<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AnalyticsEvent
 *
 * Eloquent model representing the AnalyticsEvent entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $dates
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnalyticsEvent query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name', 'event_type', 'description', 'session_id', 'user_id', 'url', 'referrer',
        'ip_address', 'country_code', 'device_type', 'browser', 'os', 'screen_resolution',
        'trackable_type', 'trackable_id', 'value', 'currency', 'properties', 'user_agent',
        'is_important', 'is_conversion', 'conversion_value', 'conversion_currency', 'notes',
        'user_name', 'user_email', 'event_data', 'utm_source', 'utm_medium', 'utm_campaign',
        'utm_term', 'utm_content', 'referrer_url', 'country', 'city', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'event_data' => 'array',
        'is_important' => 'boolean',
        'is_conversion' => 'boolean',
        'conversion_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];
    // Relationships

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle trackable functionality with proper error handling.
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    /**
     * Handle scopeByEventType functionality with proper error handling.
     */
    public function scopeByEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeBySession functionality with proper error handling.
     */
    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeWithValue functionality with proper error handling.
     */
    public function scopeWithValue(Builder $query): Builder
    {
        return $query->whereNotNull('value');
    }

    /**
     * Handle scopeRegisteredUsers functionality with proper error handling.
     */
    public function scopeRegisteredUsers(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Handle scopeAnonymousUsers functionality with proper error handling.
     */
    public function scopeAnonymousUsers(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Handle scopeByDeviceType functionality with proper error handling.
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Handle scopeByBrowser functionality with proper error handling.
     */
    public function scopeByBrowser(Builder $query, string $browser): Builder
    {
        return $query->where('browser', $browser);
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Handle scopeToday functionality with proper error handling.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Handle scopeThisWeek functionality with proper error handling.
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Handle scopeThisMonth functionality with proper error handling.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Handle scopeOfType functionality with proper error handling.
     */
    public function scopeOfType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Handle scopeForSession functionality with proper error handling.
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeInDateRange functionality with proper error handling.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors & Mutators

    /**
     * Handle getEventTypeLabelAttribute functionality with proper error handling.
     */
    public function getEventTypeLabelAttribute(): string
    {
        return __('admin.analytics.event_types.'.$this->event_type, $this->event_type);
    }

    /**
     * Handle getDeviceIconAttribute functionality with proper error handling.
     */
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => 'heroicon-o-computer-desktop',
            'mobile' => 'heroicon-o-device-phone-mobile',
            'tablet' => 'heroicon-o-device-tablet',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    /**
     * Handle getFormattedValueAttribute functionality with proper error handling.
     */
    public function getFormattedValueAttribute(): ?string
    {
        if (! $this->value) {
            return null;
        }
        $currency = $this->currency ?? 'EUR';

        return number_format($this->value, 2).' '.$currency;
    }

    /**
     * Handle getIsRegisteredUserAttribute functionality with proper error handling.
     */
    public function getIsRegisteredUserAttribute(): bool
    {
        return ! is_null($this->user_id);
    }

    /**
     * Handle getIsAnonymousUserAttribute functionality with proper error handling.
     */
    public function getIsAnonymousUserAttribute(): bool
    {
        return is_null($this->user_id);
    }

    // Static methods

    /**
     * Handle getEventTypes functionality with proper error handling.
     */
    public static function getEventTypes(): array
    {
        return ['page_view' => __('admin.analytics.event_types.page_view'), 'product_view' => __('admin.analytics.event_types.product_view'), 'add_to_cart' => __('admin.analytics.event_types.add_to_cart'), 'remove_from_cart' => __('admin.analytics.event_types.remove_from_cart'), 'purchase' => __('admin.analytics.event_types.purchase'), 'search' => __('admin.analytics.event_types.search'), 'user_register' => __('admin.analytics.event_types.user_register'), 'user_login' => __('admin.analytics.event_types.user_login'), 'user_logout' => __('admin.analytics.event_types.user_logout'), 'newsletter_signup' => __('admin.analytics.event_types.newsletter_signup'), 'contact_form' => __('admin.analytics.event_types.contact_form'), 'download' => __('admin.analytics.event_types.download'), 'video_play' => __('admin.analytics.event_types.video_play'), 'social_share' => __('admin.analytics.event_types.social_share')];
    }

    /**
     * Handle getDeviceTypes functionality with proper error handling.
     */
    public static function getDeviceTypes(): array
    {
        return ['desktop' => __('admin.analytics.device_types.desktop'), 'mobile' => __('admin.analytics.device_types.mobile'), 'tablet' => __('admin.analytics.device_types.tablet')];
    }

    /**
     * Handle getBrowsers functionality with proper error handling.
     */
    public static function getBrowsers(): array
    {
        return ['Chrome' => __('admin.analytics.browsers.chrome'), 'Firefox' => __('admin.analytics.browsers.firefox'), 'Safari' => __('admin.analytics.browsers.safari'), 'Edge' => __('admin.analytics.browsers.edge')];
    }

    /**
     * Handle getEventTypeStats functionality with proper error handling.
     */
    public static function getEventTypeStats(): array
    {
        return self::selectRaw('event_type, COUNT(*) as count')->groupBy('event_type')->orderBy('count', 'desc')->pluck('count', 'event_type')->toArray();
    }

    /**
     * Handle getDeviceTypeStats functionality with proper error handling.
     */
    public static function getDeviceTypeStats(): array
    {
        return self::selectRaw('device_type, COUNT(*) as count')->whereNotNull('device_type')->groupBy('device_type')->orderBy('count', 'desc')->pluck('count', 'device_type')->toArray();
    }

    /**
     * Handle getBrowserStats functionality with proper error handling.
     */
    public static function getBrowserStats(): array
    {
        return self::selectRaw('browser, COUNT(*) as count')->whereNotNull('browser')->groupBy('browser')->orderBy('count', 'desc')->pluck('count', 'browser')->toArray();
    }

    /**
     * Handle getRevenueStats functionality with proper error handling.
     */
    public static function getRevenueStats(): array
    {
        return self::whereNotNull('value')->selectRaw('DATE(created_at) as date, SUM(value) as revenue')->groupBy('date')->orderBy('date', 'desc')->limit(30)->pluck('revenue', 'date')->toArray();
    }

    /**
     * Handle track functionality with proper error handling.
     *
     * @param  mixed  $trackable
     */
    public static function track(string $eventType, array $data = [], $trackable = null): self
    {
        $eventData = [
            'event_type' => $eventType,
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
            'url' => request()->url(),
            'referrer' => request()->header('referer'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // If data contains properties, use them; otherwise treat the entire data as properties
        if (isset($data['properties'])) {
            $eventData['properties'] = $data['properties'];
            unset($data['properties']);
        } else {
            // Treat the data array as properties if it's not empty
            if (! empty($data)) {
                $eventData['properties'] = $data;
                $data = [];  // Clear data so it doesn't get merged again
            }
        }

        // Merge remaining data
        $eventData = array_merge($eventData, $data);

        if ($trackable && is_object($trackable)) {
            $eventData['trackable_type'] = get_class($trackable);
            $eventData['trackable_id'] = $trackable->id;
        } elseif ($trackable && is_string($trackable)) {
            // If trackable is a string (like URL), use it as URL
            $eventData['url'] = $trackable;
        }

        return self::create($eventData);
    }
}
