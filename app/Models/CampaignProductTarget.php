<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CampaignProductTargetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignProductTarget Model (Deprecated)
 *
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
final class CampaignProductTarget extends Model
{
    /** @use HasFactory<CampaignProductTargetFactory> */
    use HasFactory;

    protected $table = 'campaign_product_targets';

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

    protected $casts = [
        'conditions'  => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the campaign that owns the target.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the product being targeted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the category being targeted.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand being targeted.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the collection being targeted.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
