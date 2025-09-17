<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
/**
 * Subscriber
 * 
 * Eloquent model representing the Subscriber entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class Subscriber extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'email', 'first_name', 'last_name', 'phone', 'company', 'job_title', 'interests', 'source', 'status', 'subscribed_at', 'unsubscribed_at', 'last_email_sent_at', 'email_count', 'metadata'];
    protected $casts = ['interests' => 'array', 'metadata' => 'array', 'subscribed_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'last_email_sent_at' => 'datetime', 'email_count' => 'integer'];
    /**
     * Boot the service provider or trait functionality.
     * @return void
     */
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
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
    /**
     * Handle scopeInactive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }
    /**
     * Handle scopeUnsubscribed functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->where('status', 'unsubscribed');
    }
    /**
     * Handle scopeBySource functionality with proper error handling.
     * @param Builder $query
     * @param string $source
     * @return Builder
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }
    /**
     * Handle scopeWithInterests functionality with proper error handling.
     * @param Builder $query
     * @param array $interests
     * @return Builder
     */
    public function scopeWithInterests(Builder $query, array $interests): Builder
    {
        return $query->whereJsonContains('interests', $interests);
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param Builder $query
     * @param int $days
     * @return Builder
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('subscribed_at', '>=', now()->subDays($days));
    }
    // Accessors & Mutators
    /**
     * Handle fullName functionality with proper error handling.
     * @return Attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn() => trim($this->first_name . ' ' . $this->last_name));
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return Attribute
     */
    protected function isActive(): Attribute
    {
        return Attribute::make(get: fn() => $this->status === 'active');
    }
    /**
     * Handle isUnsubscribed functionality with proper error handling.
     * @return Attribute
     */
    protected function isUnsubscribed(): Attribute
    {
        return Attribute::make(get: fn() => $this->status === 'unsubscribed');
    }
    // Methods
    /**
     * Handle unsubscribe functionality with proper error handling.
     * @return bool
     */
    public function unsubscribe(): bool
    {
        return $this->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()]);
    }
    /**
     * Handle resubscribe functionality with proper error handling.
     * @return bool
     */
    public function resubscribe(): bool
    {
        return $this->update(['status' => 'active', 'unsubscribed_at' => null]);
    }
    /**
     * Handle incrementEmailCount functionality with proper error handling.
     * @return bool
     */
    public function incrementEmailCount(): bool
    {
        return $this->increment('email_count') && $this->update(['last_email_sent_at' => now()]);
    }
    /**
     * Handle addInterest functionality with proper error handling.
     * @param string $interest
     * @return bool
     */
    public function addInterest(string $interest): bool
    {
        $interests = $this->interests ?? [];
        if (!in_array($interest, $interests)) {
            $interests[] = $interest;
            return $this->update(['interests' => $interests]);
        }
        return true;
    }
    /**
     * Handle removeInterest functionality with proper error handling.
     * @param string $interest
     * @return bool
     */
    public function removeInterest(string $interest): bool
    {
        $interests = $this->interests ?? [];
        $interests = array_filter($interests, fn($i) => $i !== $interest);
        return $this->update(['interests' => array_values($interests)]);
    }
    /**
     * Handle hasInterest functionality with proper error handling.
     * @param string $interest
     * @return bool
     */
    public function hasInterest(string $interest): bool
    {
        return in_array($interest, $this->interests ?? []);
    }
    // Relationships
    /**
     * Handle user functionality with proper error handling.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Static methods
    /**
     * Handle subscribe functionality with proper error handling.
     * @param array $data
     * @return self
     */
    public static function subscribe(array $data): self
    {
        // Try to find existing user by email
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $data['user_id'] = $user->id;
        }
        return static::create(array_merge($data, ['status' => 'active', 'subscribed_at' => now()]));
    }
    /**
     * Handle findByEmail functionality with proper error handling.
     * @param string $email
     * @return self|null
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }
    /**
     * Handle getActiveCount functionality with proper error handling.
     * @return int
     */
    public static function getActiveCount(): int
    {
        return static::active()->count();
    }
    /**
     * Handle getRecentSubscribers functionality with proper error handling.
     * @param int $days
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentSubscribers(int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::recent($days)->get();
    }
}