<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

/**
 * Report
 *
 * Eloquent model representing the Report entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property array $translatable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Report query()
 *
 * @mixin \Eloquent
 */
final class Report extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'type', 'category', 'date_range', 'start_date', 'end_date', 'filters', 'description', 'content', 'is_active', 'is_public', 'is_scheduled', 'schedule_frequency', 'last_generated_at', 'generated_by', 'view_count', 'download_count', 'settings', 'metadata'];

    protected $casts = ['filters' => 'array', 'is_active' => 'boolean', 'is_public' => 'boolean', 'is_scheduled' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'last_generated_at' => 'datetime', 'view_count' => 'integer', 'download_count' => 'integer', 'settings' => 'array', 'metadata' => 'array'];

    public array $translatable = ['name', 'description', 'content'];

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'type', 'category', 'is_active', 'is_public'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "Report {$eventName}")->useLogName('report');
    }

    /**
     * Handle generator functionality with proper error handling.
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Handle documents functionality with proper error handling.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Handle analyticsEvents functionality with proper error handling.
     */
    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'trackable_id')->where('trackable_type', self::class);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopePublic functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Handle scopeScheduled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    /**
     * Handle scopeInDateRange functionality with proper error handling.
     *
     * @param  mixed  $query
     * @param  mixed  $startDate
     * @param  mixed  $endDate
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Handle scopePopular functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Handle getReportTypes functionality with proper error handling.
     */
    public function getReportTypes(): array
    {
        return ['sales' => __('admin.reports.types.sales'), 'products' => __('admin.reports.types.products'), 'customers' => __('admin.reports.types.customers'), 'inventory' => __('admin.reports.types.inventory'), 'analytics' => __('admin.reports.types.analytics'), 'financial' => __('admin.reports.types.financial'), 'marketing' => __('admin.reports.types.marketing'), 'custom' => __('admin.reports.types.custom')];
    }

    /**
     * Handle getReportCategories functionality with proper error handling.
     */
    public function getReportCategories(): array
    {
        return ['sales' => __('admin.reports.categories.sales'), 'marketing' => __('admin.reports.categories.marketing'), 'operations' => __('admin.reports.categories.operations'), 'finance' => __('admin.reports.categories.finance'), 'customer_service' => __('admin.reports.categories.customer_service'), 'inventory' => __('admin.reports.categories.inventory'), 'analytics' => __('admin.reports.categories.analytics')];
    }

    /**
     * Handle getDateRanges functionality with proper error handling.
     */
    public function getDateRanges(): array
    {
        return ['today' => __('admin.reports.date_ranges.today'), 'yesterday' => __('admin.reports.date_ranges.yesterday'), 'last_7_days' => __('admin.reports.date_ranges.last_7_days'), 'last_30_days' => __('admin.reports.date_ranges.last_30_days'), 'last_90_days' => __('admin.reports.date_ranges.last_90_days'), 'this_year' => __('admin.reports.date_ranges.this_year'), 'custom' => __('admin.reports.date_ranges.custom')];
    }

    /**
     * Handle getScheduleFrequencies functionality with proper error handling.
     */
    public function getScheduleFrequencies(): array
    {
        return ['daily' => __('admin.reports.schedule_frequencies.daily'), 'weekly' => __('admin.reports.schedule_frequencies.weekly'), 'monthly' => __('admin.reports.schedule_frequencies.monthly'), 'quarterly' => __('admin.reports.schedule_frequencies.quarterly'), 'yearly' => __('admin.reports.schedule_frequencies.yearly')];
    }

    /**
     * Handle incrementViewCount functionality with proper error handling.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Handle incrementDownloadCount functionality with proper error handling.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Handle isGenerated functionality with proper error handling.
     */
    public function isGenerated(): bool
    {
        return $this->last_generated_at !== null;
    }

    /**
     * Handle isScheduled functionality with proper error handling.
     */
    public function isScheduled(): bool
    {
        return $this->is_scheduled;
    }

    /**
     * Handle isPublic functionality with proper error handling.
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Handle getRouteKeyName functionality with proper error handling.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
