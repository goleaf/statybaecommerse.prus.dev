<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CampaignCustomerSegmentFactory;
use Illuminate\Database\Eloquent\Builder;
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
final class CampaignCustomerSegment extends Model
{
    use HasFactory;
    use SoftDeletes;

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
     * Provide factory resolution through HasFactory for clean test and seeder generation.
     */
    protected static function newFactory(): CampaignCustomerSegmentFactory
    {
        // Returning the typed factory keeps the Laravel convention while remaining explicit for static analysers.
        return CampaignCustomerSegmentFactory::new();
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'segment_criteria'  => 'array',
            'targeting_tags'    => 'array',
            'custom_conditions' => 'array',
            'track_performance' => 'boolean',
            'auto_optimize'     => 'boolean',
            'is_active'         => 'boolean',
            'sort_order'        => 'integer',
        ];
    }

    /**
     * Handle campaign functionality with proper error handling.
     *
     *
     * @phpstan-return BelongsTo<Campaign, CampaignCustomerSegment>
     */
    public function campaign(): BelongsTo
    {
        /** @var BelongsTo<Campaign, CampaignCustomerSegment> $relation */
        $relation = $this->belongsTo(Campaign::class);

        return $relation;
    }

    /**
     * Handle customerGroup functionality with proper error handling.
     *
     *
     * @phpstan-return BelongsTo<CustomerGroup, CampaignCustomerSegment>
     */
    public function customerGroup(): BelongsTo
    {
        /** @var BelongsTo<CustomerGroup, CampaignCustomerSegment> $relation */
        $relation = $this->belongsTo(CustomerGroup::class);

        return $relation;
    }

    /**
     * Scope the query to include only segments matching the provided type.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeOfSegmentType(Builder $query, string $segmentType): Builder
    {
        // Using an explicit scope keeps future callers expressive while ensuring segment_type filters remain reusable.
        return $query->where('segment_type', $segmentType);
    }

    /**
     * Scope the query to only include active segments.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeActive(Builder $query): Builder
    {
        // Relying on a local scope instead of a global scope preserves access to inactive rows for administrative tasks.
        return $query->where('is_active', true);
    }

    /**
     * Scope the query to only include inactive segments.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeInactive(Builder $query): Builder
    {
        // Complementary inactive scope gives tests and UI layers a clear entry point to fetch paused segments.
        return $query->where('is_active', false);
    }

    /**
     * Scope the query to segments for a specific campaign.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeForCampaign(Builder $query, int|string $campaignId): Builder
    {
        // Accepting int|string keeps compatibility with UUIDs or integer identifiers without extra casting.
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope the query to segments for a specific customer group.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeForCustomerGroup(Builder $query, int|string $customerGroupId): Builder
    {
        // Segments often pivot on customer groups; this helper centralises the filter logic for reuse.
        return $query->where('customer_group_id', $customerGroupId);
    }

    /**
     * Scope the query to order segments by their configured sort order.
     *
     * @param  Builder<CampaignCustomerSegment> $query
     * @return Builder<CampaignCustomerSegment>
     */
    public function scopeOrdered(Builder $query, string $direction = 'asc'): Builder
    {
        // Guard against invalid direction input to avoid malformed SQL while still allowing asc/desc toggles.
        $sanitisedDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy('sort_order', $sanitisedDirection);
    }
}
