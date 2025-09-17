<?php

declare (strict_types=1);
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ReferralCodeUsageLog
 * 
 * Eloquent model representing the ReferralCodeUsageLog entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeUsageLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeUsageLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReferralCodeUsageLog query()
 * @mixin \Eloquent
 */
final class ReferralCodeUsageLog extends Model
{
    use HasFactory;
    protected $fillable = ['referral_code_id', 'user_id', 'ip_address', 'user_agent', 'referrer', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
    /**
     * Handle referralCode functionality with proper error handling.
     * @return BelongsTo
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle scopeByDateRange functionality with proper error handling.
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    /**
     * Handle scopeByUser functionality with proper error handling.
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
    /**
     * Handle scopeByIp functionality with proper error handling.
     * @param Builder $query
     * @param string $ipAddress
     * @return Builder
     */
    public function scopeByIp(Builder $query, string $ipAddress): Builder
    {
        return $query->where('ip_address', $ipAddress);
    }
}
