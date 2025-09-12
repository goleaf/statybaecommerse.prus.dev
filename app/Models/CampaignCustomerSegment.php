<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CampaignCustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'customer_group_id',
        'segment_type',
        'segment_criteria',
    ];

    protected function casts(): array
    {
        return [
            'segment_criteria' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
