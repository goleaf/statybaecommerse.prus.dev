<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

final class Subscriber extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'company',
        'job_title',
        'interests',
        'source',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'last_email_sent_at',
        'email_count',
        'metadata',
    ];

    protected $casts = [
        'interests' => 'array',
        'metadata' => 'array',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'email_count' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $subscriber) {
            if (empty($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }
        });
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->where('status', 'unsubscribed');
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function scopeWithInterests(Builder $query, array $interests): Builder
    {
        return $query->whereJsonContains('interests', $interests);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('subscribed_at', '>=', now()->subDays($days));
    }

    // Accessors & Mutators
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . $this->last_name),
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'active',
        );
    }

    protected function isUnsubscribed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'unsubscribed',
        );
    }

    // Methods
    public function unsubscribe(): bool
    {
        return $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }

    public function resubscribe(): bool
    {
        return $this->update([
            'status' => 'active',
            'unsubscribed_at' => null,
        ]);
    }

    public function incrementEmailCount(): bool
    {
        return $this->increment('email_count') && 
               $this->update(['last_email_sent_at' => now()]);
    }

    public function addInterest(string $interest): bool
    {
        $interests = $this->interests ?? [];
        if (!in_array($interest, $interests)) {
            $interests[] = $interest;
            return $this->update(['interests' => $interests]);
        }
        return true;
    }

    public function removeInterest(string $interest): bool
    {
        $interests = $this->interests ?? [];
        $interests = array_filter($interests, fn($i) => $i !== $interest);
        return $this->update(['interests' => array_values($interests)]);
    }

    public function hasInterest(string $interest): bool
    {
        return in_array($interest, $this->interests ?? []);
    }

    // Static methods
    public static function subscribe(array $data): self
    {
        return static::create(array_merge($data, [
            'status' => 'active',
            'subscribed_at' => now(),
        ]));
    }

    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    public static function getActiveCount(): int
    {
        return static::active()->count();
    }

    public static function getRecentSubscribers(int $days = 30): int
    {
        return static::recent($days)->count();
    }
}
