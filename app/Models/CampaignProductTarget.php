<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignProductTarget
 *
 * Eloquent model representing the CampaignProductTarget entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignProductTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'product_id',
        'category_id',
        'brand_id',
        'collection_id',
        'target_type',
        'priority',
        'weight',
        'sort_order',
        'is_active',
        'is_featured',
        'conditions',
        'notes',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'product_id' => 'integer',
            'category_id' => 'integer',
            'brand_id' => 'integer',
            'collection_id' => 'integer',
            'priority' => 'integer',
            'weight' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
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
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle category functionality with proper error handling.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Handle brand functionality with proper error handling.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Handle collection functionality with proper error handling.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the target name based on target type.
     */
    public function getTargetNameAttribute(): ?string
    {
        return match ($this->target_type) {
            'product' => $this->product?->name,
            'category' => $this->category?->name,
            'brand' => $this->brand?->name,
            'collection' => $this->collection?->name,
            default => null,
        };
    }

    /**
     * Get the target identifier (SKU, slug, etc.) based on target type.
     */
    public function getTargetIdentifierAttribute(): ?string
    {
        return match ($this->target_type) {
            'product' => $this->product?->sku,
            'category' => $this->category?->slug,
            'brand' => $this->brand?->slug,
            'collection' => $this->collection?->slug,
            default => null,
        };
    }

    /**
     * Check if the target is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the target is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Get the target model based on target type.
     */
    public function getTargetModel(): ?Model
    {
        return match ($this->target_type) {
            'product' => $this->product,
            'category' => $this->category,
            'brand' => $this->brand,
            'collection' => $this->collection,
            default => null,
        };
    }

    /**
     * Scope for active targets.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured targets.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for high priority targets.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', '>=', 80);
    }

    /**
     * Scope for targets by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('target_type', $type);
    }

    /**
     * Scope for targets by campaign.
     */
    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }
}
