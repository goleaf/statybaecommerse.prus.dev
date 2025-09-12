<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

final class ProductHistory extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = [
        'product_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'product_histories';
    protected string $translationModel = \App\Models\Translations\ProductHistoryTranslation::class;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['action', 'field_name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "ProductHistory {$eventName}")
            ->useLogName('product_history');
    }

    // Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByField($query, string $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors & Mutators
    public function getFormattedOldValueAttribute(): string
    {
        return $this->formatValue($this->old_value);
    }

    public function getFormattedNewValueAttribute(): string
    {
        return $this->formatValue($this->new_value);
    }

    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            'created' => __('admin.product_history.actions.created'),
            'updated' => __('admin.product_history.actions.updated'),
            'deleted' => __('admin.product_history.actions.deleted'),
            'restored' => __('admin.product_history.actions.restored'),
            'price_changed' => __('admin.product_history.actions.price_changed'),
            'stock_updated' => __('admin.product_history.actions.stock_updated'),
            'status_changed' => __('admin.product_history.actions.status_changed'),
            default => $this->action,
        };
    }

    public function getFieldDisplayAttribute(): string
    {
        return __('admin.product_history.fields.' . $this->field_name, [], $this->field_name);
    }

    public function getChangeSummaryAttribute(): string
    {
        if ($this->action === 'created') {
            return __('admin.product_history.summaries.created', ['field' => $this->field_display]);
        }

        if ($this->action === 'deleted') {
            return __('admin.product_history.summaries.deleted', ['field' => $this->field_display]);
        }

        return __('admin.product_history.summaries.updated', [
            'field' => $this->field_display,
            'from' => $this->formatted_old_value,
            'to' => $this->formatted_new_value,
        ]);
    }

    // Helper methods
    private function formatValue($value): string
    {
        if (is_null($value)) {
            return __('admin.common.none');
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? __('admin.common.yes') : __('admin.common.no');
        }

        return (string) $value;
    }

    public function isSignificantChange(): bool
    {
        $significantFields = ['price', 'sale_price', 'stock_quantity', 'status', 'is_visible'];
        return in_array($this->field_name, $significantFields);
    }

    public function getChangeImpact(): string
    {
        if (!$this->isSignificantChange()) {
            return 'low';
        }

        if (in_array($this->field_name, ['price', 'sale_price', 'stock_quantity'])) {
            return 'high';
        }

        return 'medium';
    }

    // Static methods
    public static function createHistoryEntry(
        Product $product,
        string $action,
        ?string $fieldName = null,
        $oldValue = null,
        $newValue = null,
        ?string $description = null,
        ?User $user = null
    ): self {
        return self::create([
            'product_id' => $product->id,
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }

    // Translation methods
    public function getTranslatedAction(?string $locale = null): ?string
    {
        return $this->trans('action', $locale) ?: $this->action_display;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    public function getTranslatedFieldName(?string $locale = null): ?string
    {
        return $this->trans('field_name', $locale) ?: $this->field_display;
    }

    // Scope for translated histories
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this history entry
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if history entry has translation for specific locale
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ProductHistoryTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'action' => $this->action,
                'description' => $this->description,
                'field_name' => $this->field_name,
            ]
        );
    }

    // Update translation for specific locale
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);
        return $translation->update($data);
    }

    // Delete translation for specific locale
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }
}
