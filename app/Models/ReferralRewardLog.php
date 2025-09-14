<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * ReferralRewardLog
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class ReferralRewardLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_reward_id',
        'user_id',
        'action',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /**
     * Get the referral reward this log belongs to
     */
    public function referralReward(): BelongsTo
    {
        return $this->belongsTo(ReferralReward::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
