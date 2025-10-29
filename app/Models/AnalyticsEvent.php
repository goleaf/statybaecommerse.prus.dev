<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use JsonSerializable;
use Throwable;
use Traversable;

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

    protected $attributes = [
        'conversion_currency' => 'EUR',
    ];

    public function setConversionCurrencyAttribute($value): void
    {
        $currency = is_string($value) ? strtoupper(trim($value)) : $value;

        if ($currency === null || $currency === '') {
            $currency = 'EUR';
        }

        $this->attributes['conversion_currency'] = $currency;
    }

    protected $fillable = [
        'event_name',
        'event_type',
        'description',
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
        'is_important',
        'is_conversion',
        'conversion_value',
        'conversion_currency',
        'notes',
        'user_name',
        'user_email',
        'event_data',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer_url',
        'country',
        'city',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_id'          => 'integer',
        'properties'       => 'array',
        'event_data'       => 'array',
        'is_important'     => 'boolean',
        'is_conversion'    => 'boolean',
        'conversion_value' => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];
    // Relationships

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->withoutGlobalScopes([ActiveScope::class])
            ->withTrashed();
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
        return $this->withoutOwnershipScope($query)->where('event_type', $eventType);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        // Guard against leaking analytics for other customers by enforcing the authenticated user when present.
        $authenticatedUser = auth()->user();

        if ($authenticatedUser !== null && ! $authenticatedUser->is_admin && $authenticatedUser->id !== $userId) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeBySession functionality with proper error handling.
     */
    public function scopeBySession(Builder $query, string $sessionId): Builder
    {
        return $this->withoutOwnershipScope($query)->where('session_id', $sessionId);
    }

    /**
     * Handle scopeWithValue functionality with proper error handling.
     */
    public function scopeWithValue(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereNotNull('value');
    }

    /**
     * Handle scopeRegisteredUsers functionality with proper error handling.
     */
    public function scopeRegisteredUsers(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereNotNull('user_id');
    }

    /**
     * Handle scopeAnonymousUsers functionality with proper error handling.
     */
    public function scopeAnonymousUsers(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereNull('user_id');
    }

    /**
     * Handle scopeByDeviceType functionality with proper error handling.
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $this->withoutOwnershipScope($query)->where('device_type', $deviceType);
    }

    /**
     * Handle scopeByBrowser functionality with proper error handling.
     */
    public function scopeByBrowser(Builder $query, string $browser): Builder
    {
        return $this->withoutOwnershipScope($query)->where('browser', $browser);
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        $normalizedNameExpression = "NULLIF(TRIM(event_name), '')";
        $collatedNameExpression = "COALESCE($normalizedNameExpression, '')";

        return $this->withoutOwnershipScope($query)
            ->orderByRaw("CASE WHEN $normalizedNameExpression IS NULL THEN 1 ELSE 0 END")
            ->orderByRaw("LOWER($collatedNameExpression)")
            ->orderByRaw("$collatedNameExpression");
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $this->withoutOwnershipScope($query)->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Handle scopeToday functionality with proper error handling.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereDate('created_at', today());
    }

    /**
     * Handle scopeThisWeek functionality with proper error handling.
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Handle scopeThisMonth functionality with proper error handling.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $this->withoutOwnershipScope($query)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    /**
     * Handle scopeOfType functionality with proper error handling.
     */
    public function scopeOfType(Builder $query, string $eventType): Builder
    {
        return $this->withoutOwnershipScope($query)->where('event_type', $eventType);
    }

    /**
     * Handle scopeForSession functionality with proper error handling.
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $this->withoutOwnershipScope($query)->where('session_id', $sessionId);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $this->withoutOwnershipScope($query)->where('user_id', $userId);
    }

    /**
     * Handle scopeInDateRange functionality with proper error handling.
     */
    public function scopeInDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $this->withoutOwnershipScope($query)->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Accessors & Mutators

    /**
     * Canonical list of supported analytics event identifiers kept in sync with Filament resources.
     */
    public const EVENT_TYPES = [
        'page_view',
        'click',
        'form_submit',
        'purchase',
        'signup',
        'login',
        'logout',
        'search',
        'download',
        'custom',
        'product_view',
        'add_to_cart',
        'remove_from_cart',
        'user_register',
        'user_login',
        'user_logout',
        'newsletter_signup',
        'contact_form',
        'video_play',
        'social_share',
        'scroll',
    ];

    /**
     * Handle getEventTypeLabelAttribute functionality with proper error handling.
     */
    public function getEventTypeLabelAttribute(): string
    {
        $translationKey = 'admin.analytics.event_types.' . $this->event_type;

        // Allow gracefully falling back to the raw event type when the translation
        // string is missing instead of triggering translator argument exceptions.
        $translated = __($translationKey);

        return $translated === $translationKey
            ? Str::of($this->event_type)->replace('_', ' ')->title()->toString()
            : $translated;
    }

    /**
     * Handle getDeviceIconAttribute functionality with proper error handling.
     */
    public function getDeviceIconAttribute(): string
    {
        return match ($this->device_type) {
            'desktop' => 'heroicon-o-computer-desktop',
            'mobile'  => 'heroicon-o-device-phone-mobile',
            'tablet'  => 'heroicon-o-device-tablet',
            default   => 'heroicon-o-question-mark-circle',
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

        return number_format($this->value, 2) . ' ' . $currency;
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

    private function withoutOwnershipScope(Builder $query): Builder
    {
        return $query->withoutGlobalScopes([UserOwnedScope::class]);
    }

    private static function ownershiplessQuery(): Builder
    {
        return self::query()->withoutGlobalScopes([UserOwnedScope::class]);
    }

    // Static methods

    /**
     * Handle getEventTypes functionality with proper error handling.
     */
    public static function eventTypeOptions(): array
    {
        // Translate each identifier once so UI dropdowns and filters stay consistent with locale files.
        return collect(self::EVENT_TYPES)
            ->mapWithKeys(fn (string $type): array => [
                $type => __('analytics_events.types.' . $type),
            ])
            ->all();
    }

    public static function getEventTypes(): array
    {
        return ['page_view' => __('admin.analytics.event_types.page_view'), 'product_view' => __('admin.analytics.event_types.product_view'), 'add_to_cart' => __('admin.analytics.event_types.add_to_cart'), 'remove_from_cart' => __('admin.analytics.event_types.remove_from_cart'), 'purchase' => __('admin.analytics.event_types.purchase'), 'search' => __('admin.analytics.event_types.search'), 'click' => __('admin.analytics.event_types.click'), 'user_register' => __('admin.analytics.event_types.user_register'), 'user_login' => __('admin.analytics.event_types.user_login'), 'user_logout' => __('admin.analytics.event_types.user_logout'), 'newsletter_signup' => __('admin.analytics.event_types.newsletter_signup'), 'contact_form' => __('admin.analytics.event_types.contact_form'), 'download' => __('admin.analytics.event_types.download'), 'video_play' => __('admin.analytics.event_types.video_play'), 'social_share' => __('admin.analytics.event_types.social_share')];
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
        return self::queryForAdmin()
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'event_type')
            ->toArray();
    }

    /**
     * Handle getDeviceTypeStats functionality with proper error handling.
     */
    public static function getDeviceTypeStats(): array
    {
        return self::queryForAdmin()
            ->selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'device_type')
            ->toArray();
    }

    /**
     * Handle getBrowserStats functionality with proper error handling.
     */
    public static function getBrowserStats(): array
    {
        return self::queryForAdmin()
            ->selectRaw('browser, COUNT(*) as count')
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->pluck('count', 'browser')
            ->toArray();
    }

    /**
     * Handle getRevenueStats functionality with proper error handling.
     */
    public static function getRevenueStats(): array
    {
        return self::queryForAdmin()
            ->whereNotNull('value')
            ->selectRaw('DATE(created_at) as date, SUM(value) as revenue')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->pluck('revenue', 'date')
            ->map(fn ($revenue): float => (float) $revenue)
            ->toArray();
    }

    /**
     * Cast the conversion value to a float for consistent numeric comparisons.
     */
    public function getConversionValueAttribute($value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * Build a query suitable for admin-only aggregate helpers.
     */
    protected static function queryForAdmin(): Builder
    {
        return self::query()->withoutGlobalScope(UserOwnedScope::class);
    }

    /**
     * Handle track functionality with proper error handling.
     *
     * @param mixed $trackable
     */
    public static function track(string $eventType, array $data = [], $trackable = null): self
    {
        // Resolve the current HTTP request in a defensive way so console-driven
        // contexts (for example PHPUnit or artisan commands) can still capture
        // analytics records without encountering missing request bindings.
        $request = self::resolveCurrentRequest();

        // Ensure a stable session identifier even when the session manager is
        // unavailable by falling back to a deterministic UUID for analytics
        // correlation inside non-HTTP execution paths.
        $sessionId = self::resolveSessionIdentifier() ?? (string) Str::uuid();

        $eventData = [
            'event_type' => $eventType,
            'session_id' => $sessionId,
            'user_id'    => self::resolveAuthenticatedUserId(),
            'url'        => $request?->fullUrl(),
            'referrer'   => $request?->headers->get('referer'),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $propertiesPayload = [];
        $usingFallbackPropertyBag = false;

        if (array_key_exists('properties', $data)) {
            $propertiesPayload = self::normalisePropertiesPayload($data['properties']);
            unset($data['properties']);
        }

        if ($propertiesPayload === [] && $data !== []) {
            $propertiesPayload = self::normalisePropertiesPayload($data);
            $usingFallbackPropertyBag = true;
        }

        $existingProperties = self::normalisePropertiesPayload($eventData['properties'] ?? []);

        if ($propertiesPayload !== [] && $usingFallbackPropertyBag) {
            $propertiesPayload = self::discardAttributeKeysFromProperties($propertiesPayload);
        }

        $combinedProperties = $propertiesPayload !== []
            ? array_merge($existingProperties, $propertiesPayload)
            : $existingProperties;

        if ($combinedProperties !== []) {
            $eventData['properties'] = $combinedProperties;
        } else {
            unset($eventData['properties']);
        }

        $eventData = array_merge($eventData, $data);

        if ($trackable && is_object($trackable)) {
            $eventData['trackable_type'] = $trackable::class;
            $eventData['trackable_id'] = $trackable->id;
        } elseif ($trackable && is_string($trackable)) {
            // If trackable is a string (like URL), use it as URL
            $eventData['url'] = $trackable;
        }

        return self::create($eventData);
    }

    /**
     * Resolve the current HTTP request instance in a container-safe manner.
     */
    private static function resolveCurrentRequest(): ?Request
    {
        // Pull the bound request from the container when available to avoid coupling to the global helper during tests.
        $container = Container::getInstance();

        if ($container === null || ! $container->bound('request')) {
            return null;
        }

        $request = $container->make('request');

        return $request instanceof Request ? $request : null;
    }

    /**
     * Resolve the active session identifier, gracefully handling missing session drivers.
     */
    private static function resolveSessionIdentifier(): ?string
    {
        // Ask the container for the session store when it exists so console contexts do not raise runtime errors.
        $container = Container::getInstance();

        if ($container === null || ! $container->bound('session')) {
            return null;
        }

        $session = $container->make('session');

        if ($session instanceof SessionContract) {
            try {
                return $session->getId();
            } catch (Throwable) {
                // When the session backend cannot provide an identifier just fall back to null.
                return null;
            }
        }

        if (is_object($session) && method_exists($session, 'getId')) {
            try {
                return (string) $session->getId();
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Resolve the authenticated user identifier without assuming a particular guard implementation.
     */
    private static function resolveAuthenticatedUserId(): ?int
    {
        // Leverage the container binding first which keeps the logic compatible with customised guard stacks.
        $container = Container::getInstance();

        if ($container !== null && $container->bound('auth')) {
            $authFactory = $container->make('auth');

            if ($authFactory instanceof AuthFactory) {
                try {
                    $guard = $authFactory->guard();

                    if ($guard !== null && method_exists($guard, 'id')) {
                        $identifier = $guard->id();

                        return $identifier === null ? null : (int) $identifier;
                    }
                } catch (Throwable) {
                    // Ignore guard resolution failures and fall through to the helper-based fallback.
                }
            }
        }

        if (function_exists('auth')) {
            try {
                $id = auth()->id();

                return $id === null ? null : (int) $id;
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Normalize a property payload into a plain array representation.
     */
    private static function normalisePropertiesPayload(mixed $value): array
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        } elseif ($value instanceof Traversable) {
            $value = iterator_to_array($value);
        } elseif ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_object($value)) {
            $value = (array) $value;
        }

        if (! is_array($value)) {
            return $value === null ? [] : [$value];
        }

        return $value;
    }

    /**
     * Strip known model attributes from an inferred properties payload.
     */
    private static function discardAttributeKeysFromProperties(array $properties): array
    {
        static $attributeLookup = null;

        if ($attributeLookup === null) {
            $instance = new self;
            $columns = array_filter(
                array_merge(
                    $instance->getFillable(),
                    [
                        $instance->getKeyName(),
                        $instance->getCreatedAtColumn(),
                        $instance->getUpdatedAtColumn(),
                    ]
                ),
                static fn ($column): bool => is_string($column) && $column !== ''
            );
            $attributeLookup = array_fill_keys($columns, true);
        }

        return array_diff_key($properties, $attributeLookup);
    }
}
