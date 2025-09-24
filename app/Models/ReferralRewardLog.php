<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ReferralRewardLog
 *
 * Eloquent model representing the ReferralRewardLog entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralRewardLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralRewardLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralRewardLog query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class ReferralRewardLog extends Model
{
    use HasFactory;

    protected $fillable = ['referral_reward_id', 'user_id', 'action', 'data', 'ip_address', 'user_agent'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    /**
     * Handle referralReward functionality with proper error handling.
     */
    public function referralReward(): BelongsTo
    {
        return $this->belongsTo(ReferralReward::class);
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
