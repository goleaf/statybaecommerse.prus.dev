<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

final class Campaign extends Model
{
    use HasFactory, SoftDeletes;
    use HasTranslations;

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
        'is_featured',
        'send_notifications',
        'track_conversions',
        'max_uses',
        'budget_limit',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
            'is_featured' => 'boolean',
            'send_notifications' => 'boolean',
            'track_conversions' => 'boolean',
            'max_uses' => 'integer',
            'budget_limit' => 'decimal:2',
        ];
    }

    protected string $translationModel = \App\Models\Translations\CampaignTranslation::class;

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'campaign_discount');
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopeActive($query)
    {
        return $query
            ->where('status', 'active')
            ->where(function ($q) {
                $q
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeExpired($query)
    {
        return $query
            ->where('status', 'expired')
            ->orWhere(function ($q) {
                $q
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', now());
            });
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }
}
