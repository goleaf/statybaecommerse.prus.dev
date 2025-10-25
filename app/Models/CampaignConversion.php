<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\CampaignConversionTranslation;
use App\Models\Campaign;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * CampaignConversion
 *
 * Eloquent model representing the CampaignConversion entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $timestamps
 * @property mixed $translationModel
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CampaignConversion query()
 *
 * @mixin \Eloquent
 */
// Campaign conversions expose analytics across the full lifecycle, so we avoid
// attaching broad "active" scopes that would hide completed or historical rows.
final class CampaignConversion extends Model
{
    use HasFactory, HasTranslations;

    public $timestamps = true;

    public const SCOPE_COLUMN_HINTS = [
        'is_active' => false,
        'is_visible' => false,
        'is_enabled' => false,
        'status' => true,
    ];

    protected $fillable = [
        'campaign_id',
        'click_id',
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
        'is_verified',
        'is_attributed',
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

    public string $translationModel = CampaignConversionTranslation::class;

    protected static function booted(): void
    {
        self::saving(static function (self $conversion): void {
            $conversion->converted_at ??= now();

            if ($conversion->campaign_id && blank($conversion->campaign_name)) {
                $campaign = Campaign::query()->withoutGlobalScopes()->find($conversion->campaign_id);

                if ($campaign instanceof Campaign) {
                    $conversion->campaign_name = $campaign->name;
                }
            }

            $conversion->conversion_type = trim((string) $conversion->conversion_type) ?: 'purchase';
            $conversion->status = trim((string) $conversion->status) ?: 'completed';
            $conversion->source = trim((string) $conversion->source) ?: 'direct';
            $conversion->medium = trim((string) $conversion->medium) ?: 'none';
            $conversion->device_type = trim((string) $conversion->device_type) ?: 'desktop';
            $conversion->campaign_name = trim((string) $conversion->campaign_name) ?: $conversion->conversion_type;
        });
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['conversion_value' => 'decimal:2', 'conversion_data' => 'array', 'converted_at' => 'datetime', 'is_mobile' => 'boolean', 'is_tablet' => 'boolean', 'is_desktop' => 'boolean', 'is_verified' => 'boolean', 'is_attributed' => 'boolean', 'conversion_duration' => 'integer', 'page_views' => 'integer', 'time_on_site' => 'integer', 'bounce_rate' => 'decimal:2', 'assisted_conversion_value' => 'decimal:2', 'total_conversion_value' => 'decimal:2', 'conversion_rate' => 'decimal:4', 'cost_per_conversion' => 'decimal:2', 'roi' => 'decimal:4', 'roas' => 'decimal:4', 'lifetime_value' => 'decimal:2', 'customer_acquisition_cost' => 'decimal:2', 'payback_period' => 'integer', 'tags' => 'array', 'custom_attributes' => 'array', 'touchpoints' => 'array', 'conversion_path' => 'array'];
    }

    public function getTranslationModelAttribute(): string
    {
        return $this->translationModelClass();
    }

    // Relationships

    /**
     * Handle campaign functionality with proper error handling.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Handle order functionality with proper error handling.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Handle customer functionality with proper error handling.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Handle click functionality with proper error handling.
     */
    public function click(): BelongsTo
    {
        return $this->belongsTo(CampaignClick::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CampaignConversionTranslation::class);
    }

    // Scopes

    /**
     * Handle scopeByCampaign functionality with proper error handling.
     */
    public function scopeByCampaign(Builder $query, int $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('conversion_type', $type);
    }

    /**
     * Handle scopeByStatus functionality with proper error handling.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Handle scopeByDateRange functionality with proper error handling.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        $start = \App\Support\DateParser::parse($startDate)?->startOfDay();
        $end = \App\Support\DateParser::parse($endDate)?->endOfDay();

        if ($start && $end) {
            return $query->whereBetween('converted_at', [$start, $end]);
        }

        if ($start) {
            return $query->where('converted_at', '>=', $start);
        }

        if ($end) {
            return $query->where('converted_at', '<=', $end);
        }

        return $query;
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeAttributed(Builder $query): Builder
    {
        return $query->where('is_attributed', true);
    }

    public function scopeForDateRange(Builder $query, Carbon|string $startDate, Carbon|string $endDate = null): Builder
    {
        $start = $startDate instanceof Carbon ? $startDate : Carbon::parse((string) $startDate)->startOfDay();
        $end = $endDate instanceof Carbon ? $endDate : ($endDate ? Carbon::parse((string) $endDate) : null);

        if ($end !== null) {
            $end = $end->endOfDay();
        }

        return $query
            ->when($start, static fn(Builder $inner) => $inner->where('converted_at', '>=', $start))
            ->when($end, static fn(Builder $inner) => $inner->where('converted_at', '<=', $end));
    }

    /**
     * Handle scopeByDeviceType functionality with proper error handling.
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Handle scopeBySource functionality with proper error handling.
     */
    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Handle scopeByMedium functionality with proper error handling.
     */
    public function scopeByMedium(Builder $query, string $medium): Builder
    {
        return $query->where('medium', $medium);
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Always order by the stored campaign_name so marketing tables remain predictable.
        return $query->orderBy('campaign_name');
    }

    /**
     * Handle scopeHighValue functionality with proper error handling.
     */
    public function scopeHighValue(Builder $query, float $minValue = 100): Builder
    {
        return $query->where('conversion_value', '>=', $minValue);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('converted_at', '>=', now()->subDays($days));
    }

    // Accessors

    /**
     * Handle getFormattedConversionValueAttribute functionality with proper error handling.
     */
    public function getFormattedConversionValueAttribute(): string
    {
        $value = $this->conversion_value === null ? 0 : (float) $this->conversion_value;

        return '€' . number_format($value, 2, '.', ' ');
    }

    /**
     * Handle getFormattedRoiAttribute functionality with proper error handling.
     */
    public function getFormattedRoiAttribute(): string
    {
        $roi = $this->roi ?? 0.0;

        return number_format((float) $roi * 100, 2, '.', ' ') . '%';
    }

    /**
     * Handle getFormattedConversionRateAttribute functionality with proper error handling.
     */
    public function getFormattedConversionRateAttribute(): string
    {
        $rate = $this->conversion_rate ?? 0.0;

        return number_format((float) $rate * 100, 2, '.', ' ') . '%';
    }

    /**
     * Handle getDeviceTypeDisplayAttribute functionality with proper error handling.
     */
    public function getDeviceTypeDisplayAttribute(): string
    {
        $deviceType = $this->device_type ?: 'unknown';

        return trans('campaign_conversions.device_types.' . $deviceType);
    }

    /**
     * Handle getConversionTypeDisplayAttribute functionality with proper error handling.
     */
    public function getConversionTypeDisplayAttribute(): string
    {
        $type = $this->conversion_type ?: 'purchase';

        return trans('campaign_conversions.conversion_types.' . $type);
    }

    /**
     * Handle getStatusDisplayAttribute functionality with proper error handling.
     */
    public function getStatusDisplayAttribute(): string
    {
        $status = $this->status ?: 'completed';

        return trans('campaign_conversions.statuses.' . $status);
    }

    // Methods

    /**
     * Handle calculateRoi functionality with proper error handling.
     */
    public function calculateRoi(float $cost): float
    {
        if ($cost <= 0) {
            return 0.0;
        }

        $roi = ((float) $this->conversion_value - $cost) / $cost;
        $this->roi = round($roi, 2);

        return (float) $this->roi;
    }

    /**
     * Handle calculateRoas functionality with proper error handling.
     */
    public function calculateRoas(float $cost): float
    {
        if ($cost <= 0) {
            return 0.0;
        }

        $roas = (float) $this->conversion_value / $cost;

        $this->roas = round($roas, 2);

        return (float) $this->roas;
    }

    /**
     * Handle isHighValue functionality with proper error handling.
     */
    public function isHighValue(float $threshold = 100): bool
    {
        return $this->conversion_value >= $threshold;
    }

    /**
     * Handle isRecent functionality with proper error handling.
     */
    public function isRecent(int $days = 7): bool
    {
        return $this->converted_at->isAfter(now()->subDays($days));
    }

    /**
     * Handle getAttributionValue functionality with proper error handling.
     */
    public function getAttributionValue(string $model = 'last_click'): float
    {
        return match ($model) {
            'first_click' => $this->first_click_attribution ?? 0,
            'linear' => $this->linear_attribution ?? 0,
            'time_decay' => $this->time_decay_attribution ?? 0,
            'position_based' => $this->position_based_attribution ?? 0,
            'data_driven' => $this->data_driven_attribution ?? 0,
            default => $this->last_click_attribution ?? $this->conversion_value,
        };
    }
}
