<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * CampaignProductTarget
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CampaignProductTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'product_id',
        'category_id',
        'target_type',
    ];

    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'product_id' => 'integer',
            'category_id' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

