<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Notification
 *
 * Eloquent model representing the Notification entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $appends
 * @property mixed $keyType
 * @property mixed $incrementing
 *
 * @phpstan-property array<string, string> $casts
 *
 * @property-read bool $is_read
 * @property-read bool $is_urgent
 * @property-read string|null $notification_type
 * @property-read string $formatted_created_at
 * @property-read string|null $formatted_read_at
 * @property-read string|null $title
 * @property-read string|null $message
 * @property-read string|null $color
 * @property-read array<int, string> $tags
 * @property-read string|null $attachment
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 *
 * @mixin \Eloquent
 */
final class Notification extends DatabaseNotification
{
    /**
     * @use \Illuminate\Database\Eloquent\Factories\HasFactory<\Database\Factories\NotificationFactory>
     */
    use HasFactory;

    use OrdersByName;

    /**
     * Default alphabetical ordering to the notification type when generating
     * listings, ensuring predictable grouping for support teams.
     */
    protected string $nameColumn = 'type';

    /**
     * Map normalized notification types to consistent color keywords.
     *
     * @var array<string, string>
     */
    private const TYPE_COLOR_MAP = [
        'order'      => 'blue',
        'product'    => 'green',
        'user'       => 'purple',
        'system'     => 'orange',
        'payment'    => 'yellow',
        'shipping'   => 'indigo',
        'review'     => 'pink',
        'promotion'  => 'red',
        'newsletter' => 'cyan',
        'support'    => 'gray',
    ];

    /**
     * Map normalized notification types to icon identifiers consumed by the UI.
     *
     * @var array<string, string>
     */
    private const TYPE_ICON_MAP = [
        'order'      => 'heroicon-o-shopping-cart',
        'product'    => 'heroicon-o-cube',
        'user'       => 'heroicon-o-user',
        'system'     => 'heroicon-o-cog-6-tooth',
        'payment'    => 'heroicon-o-credit-card',
        'shipping'   => 'heroicon-o-truck',
        'review'     => 'heroicon-o-star',
        'promotion'  => 'heroicon-o-gift',
        'newsletter' => 'heroicon-o-envelope',
        'support'    => 'heroicon-o-lifebuoy',
    ];

    /**
     * The canonical list of types inspected when producing aggregate statistics.
     *
     * @var string[]
     */
    private const TYPE_STAT_KEYS = [
        'order',
        'product',
        'user',
        'system',
        'payment',
        'shipping',
        'review',
        'promotion',
        'newsletter',
        'support',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'user_id', 'data', 'meta', 'read_at'];

    /**
     * Catalog of attribute casts applied to persisted payload columns.
     *
     * @var array<string, string>
     */
    // @phpstan-ignore-next-line We intentionally specialize the inherited cast definitions for better IDE hints.
    protected $casts = ['data' => 'array', 'meta' => 'array', 'read_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    /**
     * @var list<string>
     */
    protected $appends = ['is_read', 'is_urgent', 'notification_type', 'formatted_created_at', 'formatted_read_at'];

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Boot the service provider or trait functionality.
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function (Notification $notification): void {
            if (! $notification->id) {
                // Ensure every notification carries a UUID primary key for cross-service traceability.
                $notification->id = (string) Str::uuid();
            }
            if ($notification->notifiable_type === User::class && ! $notification->user_id) {
                $notification->user_id = $notification->notifiable_id;
            }
        });
    }

    // Relationships
    /**
     * Handle user functionality with proper error handling.
     */
    /**
     * @return BelongsTo<User, DatabaseNotification>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, DatabaseNotification> $relation */
        $relation = $this->belongsTo(User::class, 'notifiable_id')->where('notifiable_type', User::class);

        return $relation;
    }

    // Scopes
    /**
     * Handle scopeRead functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Handle scopeUnread functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Handle scopeUrgent functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->whereJsonContains('data->urgent', true);
    }

    /**
     * Handle scopeNormal functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeNormal(Builder $query): Builder
    {
        return $query->where(function (Builder $builder): void {
            // Match notifications that explicitly declare non-urgent status or never set the flag.
            $builder
                ->whereJsonDoesntContain('data->urgent', true)
                ->orWhereNull('data->urgent');
        });
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where(function (Builder $builder) use ($type): void {
            // Honor explicit metadata stored within the payload JSON.
            $builder->whereJsonContains('data->notification_type', $type)
                ->orWhereJsonContains('data->type', $type)
                // Fallback to the notification class name to support legacy payloads.
                ->orWhere('type', 'like', sprintf('%%%sNotification', Str::studly($type)));
        });
    }

    /**
     * Handle scopeByNotificationType functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByNotificationType(Builder $query, string $notificationType): Builder
    {
        return $query->where('type', $notificationType);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('notifiable_id', $userId)->where('notifiable_type', User::class);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Handle scopeOld functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOld(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Handle scopeWithTags functionality with proper error handling.
     */
    /**
     * @param  Builder<self>      $query
     * @param  array<int, string> $tags
     * @return Builder<self>
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function (Builder $builder) use ($tags): void {
            // Build an or-chain to match notifications containing any of the requested tags.
            foreach ($tags as $tag) {
                $builder->orWhereJsonContains('data->tags', $tag);
            }
        });
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     */
    /**
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeByDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // Accessors
    /**
     * Handle isRead functionality with proper error handling.
     */
    /**
     * @return Attribute<bool, never>
     */
    protected function isRead(): Attribute
    {
        return Attribute::make(get: fn (): bool => $this->read_at !== null);
    }

    /**
     * Handle isUrgent functionality with proper error handling.
     */
    /**
     * @return Attribute<bool, never>
     */
    protected function isUrgent(): Attribute
    {
        return Attribute::make(get: fn (): bool => (bool) ($this->data['urgent'] ?? false));
    }

    /**
     * Handle notificationType functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function notificationType(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            // Prefer explicit metadata while tolerating historical payload key variations.
            $type = $this->data['notification_type'] ?? null;

            if (is_string($type) && $type !== '') {
                return strtolower($type);
            }

            $fallbackType = $this->data['type'] ?? null;

            if (is_string($fallbackType) && $fallbackType !== '') {
                $normalized = strtolower($fallbackType);
                if (array_key_exists($normalized, self::TYPE_COLOR_MAP)) {
                    return $normalized;
                }
            }

            $typeColumn = $this->getAttribute('type');

            if (! is_string($typeColumn) || $typeColumn === '') {
                return null;
            }

            // Derive a normalized slug from the notification class name when the payload lacks context.
            return Str::of(class_basename($typeColumn))
                ->replaceLast('Notification', '')
                ->lower()
                ->value();
        });
    }

    /**
     * Handle formattedCreatedAt functionality with proper error handling.
     */
    /**
     * @return Attribute<string, never>
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(get: fn (): string => ($this->created_at ?? now())->format('d/m/Y H:i'));
    }

    /**
     * Handle formattedReadAt functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function formattedReadAt(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->read_at?->format('d/m/Y H:i'));
    }

    /**
     * Handle title functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function title(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $value = $this->data['title'] ?? null;

            return is_string($value) ? $value : null;
        });
    }

    /**
     * Handle message functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function message(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $value = $this->data['message'] ?? null;

            return is_string($value) ? $value : null;
        });
    }

    /**
     * Handle color functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function color(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $value = $this->data['color'] ?? null;

            return is_string($value) ? $value : null;
        });
    }

    /**
     * Handle tags functionality with proper error handling.
     */
    /**
     * @return Attribute<array<int, string>, never>
     */
    protected function tags(): Attribute
    {
        return Attribute::make(get: function (): array {
            $tags = $this->data['tags'] ?? [];

            if (! is_array($tags)) {
                return [];
            }

            $stringTags = array_filter($tags, static fn ($tag): bool => is_string($tag));

            /** @var array<int, string> $stringTags */
            return array_values($stringTags);
        });
    }

    /**
     * Handle attachment functionality with proper error handling.
     */
    /**
     * @return Attribute<?string, never>
     */
    protected function attachment(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $value = $this->data['attachment'] ?? null;

            return is_string($value) ? $value : null;
        });
    }

    // Methods
    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Handle toggleReadStatus functionality with proper error handling.
     */
    public function toggleReadStatus(): bool
    {
        return $this->is_read ? $this->markAsUnread() : $this->markAsRead();
    }

    /**
     * Handle duplicate functionality with proper error handling.
     */
    public function duplicate(): self
    {
        $newNotification = $this->replicate();
        $newNotification->read_at = null;
        $newNotification->created_at = now();
        $newNotification->save();

        return $newNotification;
    }

    /**
     * Handle getNotificationTypeColor functionality with proper error handling.
     */
    public function getNotificationTypeColor(): string
    {
        // Defer to the curated map while maintaining a safe fallback.
        return self::TYPE_COLOR_MAP[$this->notification_type] ?? 'gray';
    }

    /**
     * Handle getNotificationTypeIcon functionality with proper error handling.
     */
    public function getNotificationTypeIcon(): string
    {
        // Resolve the icon alias expected by Filament dashboards or fall back to the generic bell glyph.
        return self::TYPE_ICON_MAP[$this->notification_type] ?? 'heroicon-o-bell';
    }

    /**
     * Handle getTimeAgo functionality with proper error handling.
     */
    public function getTimeAgo(): string
    {
        return $this->created_at?->diffForHumans() ?? '';
    }

    /**
     * Handle getReadTimeAgo functionality with proper error handling.
     */
    public function getReadTimeAgo(): ?string
    {
        return $this->read_at?->diffForHumans();
    }

    /**
     * Handle isOld functionality with proper error handling.
     */
    public function isOld(int $days = 30): bool
    {
        $createdAt = $this->created_at;

        return $createdAt !== null && $createdAt->lt(now()->subDays($days));
    }

    /**
     * Handle isRecent functionality with proper error handling.
     */
    public function isRecent(int $days = 7): bool
    {
        $createdAt = $this->created_at;

        return $createdAt !== null && $createdAt->gte(now()->subDays($days));
    }

    /**
     * Handle hasTag functionality with proper error handling.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    /**
     * Handle addTag functionality with proper error handling.
     */
    public function addTag(string $tag): bool
    {
        $tags = $this->tags;
        if (! in_array($tag, $tags)) {
            $tags[] = $tag;

            return $this->update(['data' => array_merge($this->data, ['tags' => $tags])]);
        }

        return false;
    }

    /**
     * Handle removeTag functionality with proper error handling.
     */
    public function removeTag(string $tag): bool
    {
        $tags = $this->tags;
        $key = array_search($tag, $tags);
        if ($key !== false) {
            unset($tags[$key]);

            return $this->update(['data' => array_merge($this->data, ['tags' => array_values($tags)])]);
        }

        return false;
    }

    /**
     * Handle setUrgent functionality with proper error handling.
     */
    public function setUrgent(bool $urgent = true): bool
    {
        return $this->update(['data' => array_merge($this->data, ['urgent' => $urgent])]);
    }

    /**
     * Handle setColor functionality with proper error handling.
     */
    public function setColor(string $color): bool
    {
        return $this->update(['data' => array_merge($this->data, ['color' => $color])]);
    }

    /**
     * Handle setAttachment functionality with proper error handling.
     */
    public function setAttachment(string $path): bool
    {
        return $this->update(['data' => array_merge($this->data, ['attachment' => $path])]);
    }

    // Static methods
    /**
     * Handle getStats functionality with proper error handling.
     */
    /**
     * @return array<string, int>
     */
    public static function getStats(): array
    {
        return [
            'total'      => self::query()->count(),
            'unread'     => self::query()->unread()->count(),
            'read'       => self::query()->read()->count(),
            'urgent'     => self::query()->urgent()->count(),
            'today'      => self::query()->whereDate('created_at', today())->count(),
            'this_week'  => self::query()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => self::query()->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Handle getTypeStats functionality with proper error handling.
     */
    /**
     * @return array<string, int>
     */
    public static function getTypeStats(): array
    {
        $stats = [];

        // Iterate over the canonical set so reporting remains stable even when no records exist for a type.
        foreach (self::TYPE_STAT_KEYS as $type) {
            $stats[$type] = self::query()->byType($type)->count();
        }

        return $stats;
    }

    /**
     * Handle cleanupOld functionality with proper error handling.
     */
    public static function cleanupOld(int $days = 30): int
    {
        $deleted = self::query()->old($days)->delete();
        /** @var int $deleted */

        return $deleted;
    }

    /**
     * Handle markAllAsReadForUser functionality with proper error handling.
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        $updated = self::query()->forUser($userId)->unread()->update(['read_at' => now()]);
        /** @var int $updated */

        return $updated;
    }

    /**
     * Handle markAllAsUnreadForUser functionality with proper error handling.
     */
    public static function markAllAsUnreadForUser(int $userId): int
    {
        $updated = self::query()->forUser($userId)->read()->update(['read_at' => null]);
        /** @var int $updated */

        return $updated;
    }
}
