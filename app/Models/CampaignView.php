<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
/**
 * CampaignView
 * 
 * Eloquent model representing the CampaignView entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $timestamps
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignView query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignView extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['campaign_id', 'session_id', 'ip_address', 'user_agent', 'referer', 'customer_id', 'viewed_at'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['viewed_at' => 'datetime'];
    }
    /**
     * Handle campaign functionality with proper error handling.
     * @return BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
    /**
     * Handle customer functionality with proper error handling.
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}




