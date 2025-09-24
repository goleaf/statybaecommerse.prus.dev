<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * ProductHistory
 *
 * Eloquent model representing the ProductHistory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $table
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class ProductHistory extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    protected $fillable = ['product_id', 'user_id', 'action', 'field_name', 'old_value', 'new_value', 'description', 'ip_address', 'user_agent', 'metadata', 'causer_type', 'causer_id', 'created_at', 'updated_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    protected $table = 'product_histories';

    protected string $translationModel = \App\Models\Translations\ProductHistoryTranslation::class;

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['action', 'field_name', 'description'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "ProductHistory {$eventName}")->useLogName('product_history');
    }

    // Relations
    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle causer functionality with proper error handling.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    /**
     * Handle scopeForProduct functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Handle scopeByUser functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Handle scopeByAction functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Handle scopeByField functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByField($query, string $fieldName)
    {
        return $query->where('field_name', $fieldName);
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors & Mutators
    /**
     * Handle getFormattedOldValueAttribute functionality with proper error handling.
     */
    public function getFormattedOldValueAttribute(): string
    {
        return $this->formatValue($this->old_value);
    }

    /**
     * Handle getFormattedNewValueAttribute functionality with proper error handling.
     */
    public function getFormattedNewValueAttribute(): string
    {
        return $this->formatValue($this->new_value);
    }

    /**
     * Handle getActionDisplayAttribute functionality with proper error handling.
     */
    public function getActionDisplayAttribute(): string
    {
        return match ($this->action) {
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

    /**
     * Handle getFieldDisplayAttribute functionality with proper error handling.
     */
    public function getFieldDisplayAttribute(): string
    {
        return __('admin.product_history.fields.'.$this->field_name, [], $this->field_name);
    }

    /**
     * Handle getChangeSummaryAttribute functionality with proper error handling.
     */
    public function getChangeSummaryAttribute(): string
    {
        if ($this->action === 'created') {
            return __('admin.product_history.summaries.created', ['field' => $this->field_display]);
        }
        if ($this->action === 'deleted') {
            return __('admin.product_history.summaries.deleted', ['field' => $this->field_display]);
        }

        return __('admin.product_history.summaries.updated', ['field' => $this->field_display, 'from' => $this->formatted_old_value, 'to' => $this->formatted_new_value]);
    }

    // Helper methods
    /**
     * Handle formatValue functionality with proper error handling.
     *
     * @param  mixed  $value
     */
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

    /**
     * Handle isSignificantChange functionality with proper error handling.
     */
    public function isSignificantChange(): bool
    {
        $significantFields = ['price', 'sale_price', 'stock_quantity', 'status', 'is_visible'];

        return in_array($this->field_name, $significantFields);
    }

    /**
     * Handle getChangeImpact functionality with proper error handling.
     */
    public function getChangeImpact(): string
    {
        if (! $this->isSignificantChange()) {
            return 'low';
        }
        if (in_array($this->field_name, ['price', 'sale_price', 'stock_quantity'])) {
            return 'high';
        }

        return 'medium';
    }

    // Static methods
    /**
     * Handle createHistoryEntry functionality with proper error handling.
     *
     * @param  mixed  $oldValue
     * @param  mixed  $newValue
     */
    public static function createHistoryEntry(Product $product, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null, ?string $description = null, ?User $user = null): self
    {
        $userId = $user?->id ?? auth()->id();

        return self::create([
            'product_id' => $product->id,
            'user_id' => $userId,
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
            'causer_type' => User::class,
            'causer_id' => $userId,
        ]);
    }

    // Translation methods
    /**
     * Handle getTranslatedAction functionality with proper error handling.
     */
    public function getTranslatedAction(?string $locale = null): ?string
    {
        return $this->trans('action', $locale) ?: $this->action_display;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    /**
     * Handle getTranslatedFieldName functionality with proper error handling.
     */
    public function getTranslatedFieldName(?string $locale = null): ?string
    {
        return $this->trans('field_name', $locale) ?: $this->field_display;
    }

    // Scope for translated histories
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this history entry
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if history entry has translation for specific locale
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     *
     * @return App\Models\Translations\ProductHistoryTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ProductHistoryTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['action' => $this->action, 'description' => $this->description, 'field_name' => $this->field_name]);
    }

    // Update translation for specific locale
    /**
     * Handle updateTranslation functionality with proper error handling.
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    // Delete translation for specific locale
    /**
     * Handle deleteTranslation functionality with proper error handling.
     */
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }
}
