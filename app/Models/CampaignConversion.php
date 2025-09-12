<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasTranslations;

final class CampaignConversion extends Model
{
    use HasFactory, HasTranslations;

    protected $translationModel = CampaignConversionTranslation::class;

    protected $fillable = [
        'campaign_id',
        'order_id',
        'customer_id',
        'conversion_type',
        'conversion_value',
        'session_id',
        'conversion_data',
        'converted_at',
        'status',
        'source',
        'medium',
        'campaign_name',
        'utm_content',
        'utm_term',
        'referrer',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'conversion_duration',
        'page_views',
        'time_on_site',
        'bounce_rate',
        'exit_page',
        'landing_page',
        'funnel_step',
        'attribution_model',
        'conversion_path',
        'touchpoints',
        'last_click_attribution',
        'first_click_attribution',
        'linear_attribution',
        'time_decay_attribution',
        'position_based_attribution',
        'data_driven_attribution',
        'conversion_window',
        'lookback_window',
        'assisted_conversions',
        'assisted_conversion_value',
        'total_conversions',
        'total_conversion_value',
        'conversion_rate',
        'cost_per_conversion',
        'roi',
        'roas',
        'lifetime_value',
        'customer_acquisition_cost',
        'payback_period',
        'notes',
        'tags',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'conversion_value' => 'decimal:2',
            'conversion_data' => 'array',
            'converted_at' => 'datetime',
            'is_mobile' => 'boolean',
            'is_tablet' => 'boolean',
            'is_desktop' => 'boolean',
            'conversion_duration' => 'integer',
            'page_views' => 'integer',
            'time_on_site' => 'integer',
            'bounce_rate' => 'decimal:2',
            'assisted_conversion_value' => 'decimal:2',
            'total_conversion_value' => 'decimal:2',
            'conversion_rate' => 'decimal:4',
            'cost_per_conversion' => 'decimal:2',
            'roi' => 'decimal:4',
            'roas' => 'decimal:4',
            'lifetime_value' => 'decimal:2',
            'customer_acquisition_cost' => 'decimal:2',
            'payback_period' => 'integer',
            'tags' => 'array',
            'custom_attributes' => 'array',
            'touchpoints' => 'array',
            'conversion_path' => 'array',
        ];
    }

    // Relationships
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

    // Scopes
    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('conversion_type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('converted_at', [$startDate, $endDate]);
    }

    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function scopeByMedium(Builder $query, string $medium): Builder
    {
        return $query->where('medium', $medium);
    }

    public function scopeHighValue(Builder $query, float $minValue = 100): Builder
    {
        return $query->where('conversion_value', '>=', $minValue);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('converted_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedConversionValueAttribute(): string
    {
        return '€' . number_format($this->conversion_value, 2);
    }

    public function getFormattedRoiAttribute(): string
    {
        return number_format($this->roi * 100, 2) . '%';
    }

    public function getFormattedConversionRateAttribute(): string
    {
        return number_format($this->conversion_rate * 100, 2) . '%';
    }

    public function getDeviceTypeDisplayAttribute(): string
    {
        return match($this->device_type) {
            'mobile' => __('campaign_conversions.device_types.mobile'),
            'tablet' => __('campaign_conversions.device_types.tablet'),
            'desktop' => __('campaign_conversions.device_types.desktop'),
            default => __('campaign_conversions.device_types.unknown'),
        };
    }

    public function getConversionTypeDisplayAttribute(): string
    {
        return __('campaign_conversions.conversion_types.' . $this->conversion_type);
    }

    public function getStatusDisplayAttribute(): string
    {
        return __('campaign_conversions.statuses.' . $this->status);
    }

    // Methods
    public function calculateRoi(float $cost): float
    {
        if ($cost <= 0) {
            return 0;
        }
        
        return ($this->conversion_value - $cost) / $cost;
    }

    public function calculateRoas(float $cost): float
    {
        if ($cost <= 0) {
            return 0;
        }
        
        return $this->conversion_value / $cost;
    }

    public function isHighValue(float $threshold = 100): bool
    {
        return $this->conversion_value >= $threshold;
    }

    public function isRecent(int $days = 7): bool
    {
        return $this->converted_at->isAfter(now()->subDays($days));
    }

    public function getAttributionValue(string $model = 'last_click'): float
    {
        return match($model) {
            'first_click' => $this->first_click_attribution ?? 0,
            'linear' => $this->linear_attribution ?? 0,
            'time_decay' => $this->time_decay_attribution ?? 0,
            'position_based' => $this->position_based_attribution ?? 0,
            'data_driven' => $this->data_driven_attribution ?? 0,
            default => $this->last_click_attribution ?? $this->conversion_value,
        };
    }
}







