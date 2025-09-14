<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy([ActiveScope::class])]
final /**
 * CampaignSchedule
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class CampaignSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'schedule_type',
        'schedule_config',
        'next_run_at',
        'last_run_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schedule_config' => 'array',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

