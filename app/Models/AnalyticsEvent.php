<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final /**
 * AnalyticsEvent
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'session_id',
        'user_id',
        'url',
        'referrer',
        'ip_address',
        'country_code',
        'device_type',
        'browser',
        'os',
        'screen_resolution',
        'trackable_type',
        'trackable_id',
        'value',
        'currency',
        'properties',
        'user_agent',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByEventType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeWithValue(Builder $query): Builder
    {
        return $query->whereNotNull('value');
    }

    public function scopeRegisteredUsers(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeAnonymousUsers(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByBrowser(Builder $query, string $browser): Builder
    {
        return $query->where('browser', $browser);
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month);
    }

    // Accessors & Mutators
    public function getEventTypeLabelAttribute(): string
    {
        return __('admin.analytics.event_types.'.$this->event_type, $this->event_type);
    }

    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => 'heroicon-o-computer-desktop',
            'mobile' => 'heroicon-o-device-phone-mobile',
            'tablet' => 'heroicon-o-device-tablet',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    public function getFormattedValueAttribute(): ?string
    {
        if (! $this->value) {
            return null;
        }

        $currency = $this->currency ?? 'EUR';

        return number_format($this->value, 2).' '.$currency;
    }

    public function getIsRegisteredUserAttribute(): bool
    {
        return ! is_null($this->user_id);
    }

    public function getIsAnonymousUserAttribute(): bool
    {
        return is_null($this->user_id);
    }

    // Static methods
    public static function getEventTypes(): array
    {
        return [
            'page_view' => __('admin.analytics.event_types.page_view'),
            'product_view' => __('admin.analytics.event_types.product_view'),
            'add_to_cart' => __('admin.analytics.event_types.add_to_cart'),
            'remove_from_cart' => __('admin.analytics.event_types.remove_from_cart'),
            'purchase' => __('admin.analytics.event_types.purchase'),
            'search' => __('admin.analytics.event_types.search'),
            'user_register' => __('admin.analytics.event_types.user_register'),
            'user_login' => __('admin.analytics.event_types.user_login'),
            'user_logout' => __('admin.analytics.event_types.user_logout'),
            'newsletter_signup' => __('admin.analytics.event_types.newsletter_signup'),
            'contact_form' => __('admin.analytics.event_types.contact_form'),
            'download' => __('admin.analytics.event_types.download'),
            'video_play' => __('admin.analytics.event_types.video_play'),
            'social_share' => __('admin.analytics.event_types.social_share'),
        ];
    }

    public static function getDeviceTypes(): array
    {
        return [
            'desktop' => __('admin.analytics.device_types.desktop'),
            'mobile' => __('admin.analytics.device_types.mobile'),
            'tablet' => __('admin.analytics.device_types.tablet'),
        ];
    }

    public static function getBrowsers(): array
    {
        return [
            'Chrome' => __('admin.analytics.browsers.chrome'),
            'Firefox' => __('admin.analytics.browsers.firefox'),
            'Safari' => __('admin.analytics.browsers.safari'),
            'Edge' => __('admin.analytics.browsers.edge'),
        ];
    }

    public static function getEventTypeStats(): array
    {
        return self::selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'event_type')
            ->toArray();
    }

    public static function getDeviceTypeStats(): array
    {
        return self::selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'device_type')
            ->toArray();
    }

    public static function getBrowserStats(): array
    {
        return self::selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->pluck('count', 'browser')
            ->toArray();
    }

    public static function getRevenueStats(): array
    {
        return self::whereNotNull('value')
            ->selectRaw('DATE(created_at) as date, SUM(value) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('revenue', 'date')
            ->toArray();
    }
}
