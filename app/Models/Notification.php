<?php

declare (strict_types=1);
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotification;
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
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @mixin \Eloquent
 */
final class Notification extends DatabaseNotification
{
    use HasFactory;
    protected $fillable = ['type', 'notifiable_type', 'notifiable_id', 'user_id', 'data', 'read_at'];
    protected $casts = ['data' => 'array', 'read_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    protected $appends = ['is_read', 'is_urgent', 'notification_type', 'formatted_created_at', 'formatted_read_at'];
    protected $keyType = 'string';
    public $incrementing = false;
    /**
     * Boot the service provider or trait functionality.
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Notification $notification): void {
            if (!$notification->id) {
                $notification->id = (string) \Illuminate\Support\Str::uuid();
            }
            if ($notification->notifiable_type === User::class && !$notification->user_id) {
                $notification->user_id = $notification->notifiable_id;
            }
        });
    }
    // Relationships
    /**
     * Handle notifiable functionality with proper error handling.
     * @return MorphTo
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id')->where('notifiable_type', User::class);
    }
    // Scopes
    /**
     * Handle scopeRead functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }
    /**
     * Handle scopeUnread functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
    /**
     * Handle scopeUrgent functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->whereJsonContains('data->urgent', true);
    }
    /**
     * Handle scopeNormal functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeNormal(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereJsonDoesntContain('data->urgent', true)->orWhereNull('data->urgent');
        });
    }
    /**
     * Handle scopeByType functionality with proper error handling.
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('data->type', $type);
    }
    /**
     * Handle scopeByNotificationType functionality with proper error handling.
     * @param Builder $query
     * @param string $notificationType
     * @return Builder
     */
    public function scopeByNotificationType(Builder $query, string $notificationType): Builder
    {
        return $query->where('type', $notificationType);
    }
    /**
     * Handle scopeForUser functionality with proper error handling.
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('notifiable_id', $userId)->where('notifiable_type', User::class);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    /**
     * Handle scopeOld functionality with proper error handling.
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeOld(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }
    /**
     * Handle scopeWithTags functionality with proper error handling.
     * @param Builder $query
     * @param array $tags
     * @return Builder
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('data->tags', $tag);
            }
        });
    }
    /**
     * Handle scopeByDateRange functionality with proper error handling.
     * @param Builder $query
     * @param Carbon $from
     * @param Carbon $to
     * @return Builder
     */
    public function scopeByDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
    // Accessors
    /**
     * Handle isRead functionality with proper error handling.
     * @return Attribute
     */
    protected function isRead(): Attribute
    {
        return Attribute::make(get: fn(): bool => !is_null($this->read_at));
    }
    /**
     * Handle isUrgent functionality with proper error handling.
     * @return Attribute
     */
    protected function isUrgent(): Attribute
    {
        return Attribute::make(get: fn(): bool => $this->data['urgent'] ?? false);
    }
    /**
     * Handle notificationType functionality with proper error handling.
     * @return Attribute
     */
    protected function notificationType(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->data['type'] ?? null);
    }
    /**
     * Handle formattedCreatedAt functionality with proper error handling.
     * @return Attribute
     */
    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(get: fn(): string => $this->created_at->format('d/m/Y H:i'));
    }
    /**
     * Handle formattedReadAt functionality with proper error handling.
     * @return Attribute
     */
    protected function formattedReadAt(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->read_at?->format('d/m/Y H:i'));
    }
    /**
     * Handle title functionality with proper error handling.
     * @return Attribute
     */
    protected function title(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->data['title'] ?? null);
    }
    /**
     * Handle message functionality with proper error handling.
     * @return Attribute
     */
    protected function message(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->data['message'] ?? null);
    }
    /**
     * Handle color functionality with proper error handling.
     * @return Attribute
     */
    protected function color(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->data['color'] ?? null);
    }
    /**
     * Handle tags functionality with proper error handling.
     * @return Attribute
     */
    protected function tags(): Attribute
    {
        return Attribute::make(get: fn(): array => $this->data['tags'] ?? []);
    }
    /**
     * Handle attachment functionality with proper error handling.
     * @return Attribute
     */
    protected function attachment(): Attribute
    {
        return Attribute::make(get: fn(): ?string => $this->data['attachment'] ?? null);
    }
    // Methods
    /**
     * Handle markAsRead functionality with proper error handling.
     * @return bool
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }
    /**
     * Handle markAsUnread functionality with proper error handling.
     * @return bool
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }
    /**
     * Handle toggleReadStatus functionality with proper error handling.
     * @return bool
     */
    public function toggleReadStatus(): bool
    {
        return $this->is_read ? $this->markAsUnread() : $this->markAsRead();
    }
    /**
     * Handle duplicate functionality with proper error handling.
     * @return self
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
     * @return string
     */
    public function getNotificationTypeColor(): string
    {
        return match ($this->notification_type) {
            'order' => 'blue',
            'product' => 'green',
            'user' => 'purple',
            'system' => 'orange',
            'payment' => 'yellow',
            'shipping' => 'indigo',
            'review' => 'pink',
            'promotion' => 'red',
            'newsletter' => 'cyan',
            'support' => 'gray',
            default => 'gray',
        };
    }
    /**
     * Handle getNotificationTypeIcon functionality with proper error handling.
     * @return string
     */
    public function getNotificationTypeIcon(): string
    {
        return match ($this->notification_type) {
            'order' => 'heroicon-o-shopping-cart',
            'product' => 'heroicon-o-cube',
            'user' => 'heroicon-o-user',
            'system' => 'heroicon-o-cog-6-tooth',
            'payment' => 'heroicon-o-credit-card',
            'shipping' => 'heroicon-o-truck',
            'review' => 'heroicon-o-star',
            'promotion' => 'heroicon-o-gift',
            'newsletter' => 'heroicon-o-envelope',
            'support' => 'heroicon-o-lifebuoy',
            default => 'heroicon-o-bell',
        };
    }
    /**
     * Handle getTimeAgo functionality with proper error handling.
     * @return string
     */
    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }
    /**
     * Handle getReadTimeAgo functionality with proper error handling.
     * @return string|null
     */
    public function getReadTimeAgo(): ?string
    {
        return $this->read_at?->diffForHumans();
    }
    /**
     * Handle isOld functionality with proper error handling.
     * @param int $days
     * @return bool
     */
    public function isOld(int $days = 30): bool
    {
        return $this->created_at->lt(now()->subDays($days));
    }
    /**
     * Handle isRecent functionality with proper error handling.
     * @param int $days
     * @return bool
     */
    public function isRecent(int $days = 7): bool
    {
        return $this->created_at->gte(now()->subDays($days));
    }
    /**
     * Handle hasTag functionality with proper error handling.
     * @param string $tag
     * @return bool
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }
    /**
     * Handle addTag functionality with proper error handling.
     * @param string $tag
     * @return bool
     */
    public function addTag(string $tag): bool
    {
        $tags = $this->tags;
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            return $this->update(['data' => array_merge($this->data, ['tags' => $tags])]);
        }
        return false;
    }
    /**
     * Handle removeTag functionality with proper error handling.
     * @param string $tag
     * @return bool
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
     * @param bool $urgent
     * @return bool
     */
    public function setUrgent(bool $urgent = true): bool
    {
        return $this->update(['data' => array_merge($this->data, ['urgent' => $urgent])]);
    }
    /**
     * Handle setColor functionality with proper error handling.
     * @param string $color
     * @return bool
     */
    public function setColor(string $color): bool
    {
        return $this->update(['data' => array_merge($this->data, ['color' => $color])]);
    }
    /**
     * Handle setAttachment functionality with proper error handling.
     * @param string $path
     * @return bool
     */
    public function setAttachment(string $path): bool
    {
        return $this->update(['data' => array_merge($this->data, ['attachment' => $path])]);
    }
    // Static methods
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    public static function getStats(): array
    {
        return ['total' => self::count(), 'unread' => self::unread()->count(), 'read' => self::read()->count(), 'urgent' => self::urgent()->count(), 'today' => self::whereDate('created_at', today())->count(), 'this_week' => self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(), 'this_month' => self::whereMonth('created_at', now()->month)->count()];
    }
    /**
     * Handle getTypeStats functionality with proper error handling.
     * @return array
     */
    public static function getTypeStats(): array
    {
        $types = ['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support'];
        $stats = [];
        foreach ($types as $type) {
            $stats[$type] = self::byType($type)->count();
        }
        return $stats;
    }
    /**
     * Handle cleanupOld functionality with proper error handling.
     * @param int $days
     * @return int
     */
    public static function cleanupOld(int $days = 30): int
    {
        return self::old($days)->delete();
    }
    /**
     * Handle markAllAsReadForUser functionality with proper error handling.
     * @param int $userId
     * @return int
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->update(['read_at' => now()]);
    }
    /**
     * Handle markAllAsUnreadForUser functionality with proper error handling.
     * @param int $userId
     * @return int
     */
    public static function markAllAsUnreadForUser(int $userId): int
    {
        return self::forUser($userId)->read()->update(['read_at' => null]);
    }
}