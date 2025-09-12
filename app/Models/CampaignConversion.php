<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CampaignConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'order_id',
        'customer_id',
        'conversion_type',
        'conversion_value',
        'session_id',
        'conversion_data',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'conversion_value' => 'decimal:2',
            'conversion_data' => 'array',
            'converted_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
