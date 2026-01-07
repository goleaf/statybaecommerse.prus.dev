<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Scopes\ActiveScope;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Campaign Model (Deprecated)
 * 
 * This model maps to the discount_campaigns table for backward compatibility.
 * The campaigns feature has been removed but the model is kept to prevent
 * fatal errors in existing code.
 * 
 * @deprecated This model is deprecated as the campaigns feature has been removed.
 */
#[ScopedBy([ActiveScope::class])]
final class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'discount_campaigns';

    protected $fillable = [
        'name',
        'slug', 
        'starts_at',
        'ends_at',
        'channel_id',
        'zone_id',
        'status',
        'metadata',
        'is_active',
        'deprecated_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'deprecated_at' => 'datetime',
    ];

    public array $translatable = [
        'name',
        'description',
        'subject',
        'content',
        'cta_text',
        'banner_alt_text',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the discounts associated with this campaign.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'campaign_discount');
    }

    /**
     * Get the products associated with this campaign.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'campaign_products');
    }

    /**
     * Get the categories associated with this campaign.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'campaign_categories');
    }

    /**
     * Get the customer groups associated with this campaign.
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'campaign_customer_groups');
    }

    /**
     * Get the campaign clicks.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(CampaignClick::class);
    }

    /**
     * Get the campaign views.
     */
    public function views(): HasMany
    {
        return $this->hasMany(CampaignView::class);
    }

    /**
     * Get the campaign conversions.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(CampaignConversion::class);
    }

    /**
     * Get the campaign schedules.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(CampaignSchedule::class);
    }

    /**
     * Check if the campaign is currently active.
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               $this->status === 'active' &&
               (!$this->starts_at || $this->starts_at->isPast()) &&
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Mark campaign as deprecated.
     */
    public function markAsDeprecated(): void
    {
        $this->update([
            'deprecated_at' => now(),
            'is_active' => false,
            'status' => 'deprecated',
        ]);
    }
}