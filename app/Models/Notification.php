<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotification;

final class Notification extends DatabaseNotification
{
    use HasFactory;

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_read',
        'is_urgent',
        'notification_type',
        'formatted_created_at',
        'formatted_read_at',
    ];

    // Relationships
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notifiable_id')
            ->where('notifiable_type', User::class);
    }

    // Scopes
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->whereJsonContains('data->urgent', true);
    }

    public function scopeNormal(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereJsonDoesntContain('data->urgent', true)
                ->orWhereNull('data->urgent');
        });
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('data->type', $type);
    }

    public function scopeByNotificationType(Builder $query, string $notificationType): Builder
    {
        return $query->where('type', $notificationType);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('notifiable_id', $userId)
            ->where('notifiable_type', User::class);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOld(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('data->tags', $tag);
            }
        });
    }

    public function scopeByDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // Accessors
    protected function isRead(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => ! is_null($this->read_at),
        );
    }

    protected function isUrgent(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->data['urgent'] ?? false,
        );
    }

    protected function notificationType(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->data['type'] ?? null,
        );
    }

    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->created_at->format('d/m/Y H:i'),
        );
    }

    protected function formattedReadAt(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->read_at?->format('d/m/Y H:i'),
        );
    }

    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->data['title'] ?? null,
        );
    }

    protected function message(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->data['message'] ?? null,
        );
    }

    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->data['color'] ?? null,
        );
    }

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn (): array => $this->data['tags'] ?? [],
        );
    }

    protected function attachment(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->data['attachment'] ?? null,
        );
    }

    // Methods
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    public function toggleReadStatus(): bool
    {
        return $this->is_read ? $this->markAsUnread() : $this->markAsRead();
    }

    public function duplicate(): self
    {
        $newNotification = $this->replicate();
        $newNotification->read_at = null;
        $newNotification->created_at = now();
        $newNotification->save();

        return $newNotification;
    }

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

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getReadTimeAgo(): ?string
    {
        return $this->read_at?->diffForHumans();
    }

    public function isOld(int $days = 30): bool
    {
        return $this->created_at->lt(now()->subDays($days));
    }

    public function isRecent(int $days = 7): bool
    {
        return $this->created_at->gte(now()->subDays($days));
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    public function addTag(string $tag): bool
    {
        $tags = $this->tags;
        if (! in_array($tag, $tags)) {
            $tags[] = $tag;

            return $this->update(['data' => array_merge($this->data, ['tags' => $tags])]);
        }

        return false;
    }

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

    public function setUrgent(bool $urgent = true): bool
    {
        return $this->update(['data' => array_merge($this->data, ['urgent' => $urgent])]);
    }

    public function setColor(string $color): bool
    {
        return $this->update(['data' => array_merge($this->data, ['color' => $color])]);
    }

    public function setAttachment(string $path): bool
    {
        return $this->update(['data' => array_merge($this->data, ['attachment' => $path])]);
    }

    // Static methods
    public static function getStats(): array
    {
        return [
            'total' => self::count(),
            'unread' => self::unread()->count(),
            'read' => self::read()->count(),
            'urgent' => self::urgent()->count(),
            'today' => self::whereDate('created_at', today())->count(),
            'this_week' => self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => self::whereMonth('created_at', now()->month)->count(),
        ];
    }

    public static function getTypeStats(): array
    {
        $types = ['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support'];
        $stats = [];

        foreach ($types as $type) {
            $stats[$type] = self::byType($type)->count();
        }

        return $stats;
    }

    public static function cleanupOld(int $days = 30): int
    {
        return self::old($days)->delete();
    }

    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->update(['read_at' => now()]);
    }

    public static function markAllAsUnreadForUser(int $userId): int
    {
        return self::forUser($userId)->read()->update(['read_at' => null]);
    }
}
