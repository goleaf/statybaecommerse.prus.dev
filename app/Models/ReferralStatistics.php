<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralStatistics
 *
 * Eloquent model representing the ReferralStatistics entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralStatistics query()
 *
 * @mixin \Eloquent
 */
final class ReferralStatistics extends Model
{
    use HasFactory;

    protected $table = 'referral_statistics';

    protected $fillable = ['user_id', 'date', 'total_referrals', 'completed_referrals', 'pending_referrals', 'total_rewards_earned', 'total_discounts_given', 'metadata'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['date' => 'date', 'total_referrals' => 'integer', 'completed_referrals' => 'integer', 'pending_referrals' => 'integer', 'total_rewards_earned' => 'decimal:2', 'total_discounts_given' => 'decimal:2', 'metadata' => 'array'];
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle scopeDateRange functionality with proper error handling.
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Handle scopeForUser functionality with proper error handling.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle getOrCreateForUserAndDate functionality with proper error handling.
     */
    public static function getOrCreateForUserAndDate(int $userId, string $date): self
    {
        return \DB::transaction(function () use ($userId, $date) {
            $existing = self::where('user_id', $userId)->where('date', $date)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }
            try {
                return self::create(['user_id' => $userId, 'date' => $date, 'total_referrals' => 0, 'completed_referrals' => 0, 'pending_referrals' => 0, 'total_rewards_earned' => 0, 'total_discounts_given' => 0]);
            } catch (\Illuminate\Database\QueryException $e) {
                // If the record was created by another process, fetch it
                if ($e->getCode() === '23000') {
                    // Integrity constraint violation
                    return self::where('user_id', $userId)->where('date', $date)->firstOrFail();
                }
                throw $e;
            }
        });
    }

    /**
     * Handle incrementReferrals functionality with proper error handling.
     */
    public function incrementReferrals(): void
    {
        $this->increment('total_referrals');
        $this->increment('pending_referrals');
    }

    /**
     * Handle completeReferral functionality with proper error handling.
     */
    public function completeReferral(): void
    {
        $this->increment('completed_referrals');
        $this->decrement('pending_referrals');
    }

    /**
     * Handle addRewardEarned functionality with proper error handling.
     */
    public function addRewardEarned(float $amount): void
    {
        $this->increment('total_rewards_earned', $amount);
    }

    /**
     * Handle addDiscountGiven functionality with proper error handling.
     */
    public function addDiscountGiven(float $amount): void
    {
        $this->increment('total_discounts_given', $amount);
    }

    /**
     * Handle getTotalForUser functionality with proper error handling.
     */
    public static function getTotalForUser(int $userId): array
    {
        $stats = self::where('user_id', $userId)->selectRaw('
                SUM(total_referrals) as total_referrals,
                SUM(completed_referrals) as completed_referrals,
                SUM(pending_referrals) as pending_referrals,
                SUM(total_rewards_earned) as total_rewards_earned,
                SUM(total_discounts_given) as total_discounts_given
            ')->first();

        return ['total_referrals' => (int) ($stats->total_referrals ?? 0), 'completed_referrals' => (int) ($stats->completed_referrals ?? 0), 'pending_referrals' => (int) ($stats->pending_referrals ?? 0), 'total_rewards_earned' => (float) ($stats->total_rewards_earned ?? 0), 'total_discounts_given' => (float) ($stats->total_discounts_given ?? 0)];
    }
}
