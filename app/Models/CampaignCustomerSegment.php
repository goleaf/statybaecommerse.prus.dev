<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CampaignCustomerSegment
 *
 * Eloquent model representing the CampaignCustomerSegment entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignCustomerSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignCustomerSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignCustomerSegment query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignCustomerSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'customer_group_id',
        'segment_type',
        'segment_criteria',
        'targeting_tags',
        'custom_conditions',
        'track_performance',
        'auto_optimize',
        'is_active',
        'sort_order',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'segment_criteria' => 'array',
            'targeting_tags' => 'array',
            'track_performance' => 'boolean',
            'auto_optimize' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Handle campaign functionality with proper error handling.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Handle customerGroup functionality with proper error handling.
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
