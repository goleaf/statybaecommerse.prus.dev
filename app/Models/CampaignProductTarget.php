<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * CampaignProductTarget
 * 
 * Eloquent model representing the CampaignProductTarget entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignProductTarget query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class CampaignProductTarget extends Model
{
    use HasFactory;
    protected $fillable = ['campaign_id', 'product_id', 'category_id', 'target_type'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['campaign_id' => 'integer', 'product_id' => 'integer', 'category_id' => 'integer'];
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
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    /**
     * Handle category functionality with proper error handling.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

