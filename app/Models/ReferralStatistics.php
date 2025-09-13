<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReferralStatistics extends Model
{
    use HasFactory;

    protected $table = 'referral_statistics';

    protected $fillable = [
        'user_id',
        'date',
        'total_referrals',
        'completed_referrals',
        'pending_referrals',
        'total_rewards_earned',
        'total_discounts_given',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_referrals' => 'integer',
            'completed_referrals' => 'integer',
            'pending_referrals' => 'integer',
            'total_rewards_earned' => 'decimal:2',
            'total_discounts_given' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user these statistics belong to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific date range
     */
    public function scopeDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get or create statistics for a user and date
     */
    public static function getOrCreateForUserAndDate(int $userId, string $date): self
    {
        return \DB::transaction(function () use ($userId, $date) {
            $existing = self::where('user_id', $userId)
                ->where('date', $date)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            try {
                return self::create([
                    'user_id' => $userId,
                    'date' => $date,
                    'total_referrals' => 0,
                    'completed_referrals' => 0,
                    'pending_referrals' => 0,
                    'total_rewards_earned' => 0,
                    'total_discounts_given' => 0,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // If the record was created by another process, fetch it
                if ($e->getCode() === '23000') { // Integrity constraint violation
                    return self::where('user_id', $userId)
                        ->where('date', $date)
                        ->firstOrFail();
                }
                throw $e;
            }
        });
    }

    /**
     * Update statistics when a referral is created
     */
    public function incrementReferrals(): void
    {
        $this->increment('total_referrals');
        $this->increment('pending_referrals');
    }

    /**
     * Update statistics when a referral is completed
     */
    public function completeReferral(): void
    {
        $this->increment('completed_referrals');
        $this->decrement('pending_referrals');
    }

    /**
     * Add reward amount to statistics
     */
    public function addRewardEarned(float $amount): void
    {
        $this->increment('total_rewards_earned', $amount);
    }

    /**
     * Add discount amount to statistics
     */
    public function addDiscountGiven(float $amount): void
    {
        $this->increment('total_discounts_given', $amount);
    }

    /**
     * Get total statistics for a user
     */
    public static function getTotalForUser(int $userId): array
    {
        $stats = self::where('user_id', $userId)
            ->selectRaw('
                SUM(total_referrals) as total_referrals,
                SUM(completed_referrals) as completed_referrals,
                SUM(pending_referrals) as pending_referrals,
                SUM(total_rewards_earned) as total_rewards_earned,
                SUM(total_discounts_given) as total_discounts_given
            ')
            ->first();

        return [
            'total_referrals' => (int) ($stats->total_referrals ?? 0),
            'completed_referrals' => (int) ($stats->completed_referrals ?? 0),
            'pending_referrals' => (int) ($stats->pending_referrals ?? 0),
            'total_rewards_earned' => (float) ($stats->total_rewards_earned ?? 0),
            'total_discounts_given' => (float) ($stats->total_discounts_given ?? 0),
        ];
    }
}
