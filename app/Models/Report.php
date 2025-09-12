<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

final class Report extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'category',
        'date_range',
        'start_date',
        'end_date',
        'filters',
        'description',
        'content',
        'is_active',
        'is_public',
        'is_scheduled',
        'schedule_frequency',
        'last_generated_at',
        'generated_by',
        'view_count',
        'download_count',
        'settings',
        'metadata',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_scheduled' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated_at' => 'datetime',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    public array $translatable = [
        'name',
        'description',
        'content',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'category', 'is_active', 'is_public'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Report {$eventName}")
            ->useLogName('report');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class, 'trackable_id')
            ->where('trackable_type', self::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function getReportTypes(): array
    {
        return [
            'sales' => __('admin.reports.types.sales'),
            'products' => __('admin.reports.types.products'),
            'customers' => __('admin.reports.types.customers'),
            'inventory' => __('admin.reports.types.inventory'),
            'analytics' => __('admin.reports.types.analytics'),
            'financial' => __('admin.reports.types.financial'),
            'marketing' => __('admin.reports.types.marketing'),
            'custom' => __('admin.reports.types.custom'),
        ];
    }

    public function getReportCategories(): array
    {
        return [
            'sales' => __('admin.reports.categories.sales'),
            'marketing' => __('admin.reports.categories.marketing'),
            'operations' => __('admin.reports.categories.operations'),
            'finance' => __('admin.reports.categories.finance'),
            'customer_service' => __('admin.reports.categories.customer_service'),
            'inventory' => __('admin.reports.categories.inventory'),
            'analytics' => __('admin.reports.categories.analytics'),
        ];
    }

    public function getDateRanges(): array
    {
        return [
            'today' => __('admin.reports.date_ranges.today'),
            'yesterday' => __('admin.reports.date_ranges.yesterday'),
            'last_7_days' => __('admin.reports.date_ranges.last_7_days'),
            'last_30_days' => __('admin.reports.date_ranges.last_30_days'),
            'last_90_days' => __('admin.reports.date_ranges.last_90_days'),
            'this_year' => __('admin.reports.date_ranges.this_year'),
            'custom' => __('admin.reports.date_ranges.custom'),
        ];
    }

    public function getScheduleFrequencies(): array
    {
        return [
            'daily' => __('admin.reports.schedule_frequencies.daily'),
            'weekly' => __('admin.reports.schedule_frequencies.weekly'),
            'monthly' => __('admin.reports.schedule_frequencies.monthly'),
            'quarterly' => __('admin.reports.schedule_frequencies.quarterly'),
            'yearly' => __('admin.reports.schedule_frequencies.yearly'),
        ];
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function isGenerated(): bool
    {
        return $this->last_generated_at !== null;
    }

    public function isScheduled(): bool
    {
        return $this->is_scheduled;
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
