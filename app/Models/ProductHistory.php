<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use App\Traits\HasTranslations;
use Eloquent;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * ProductHistory
 *
 * Eloquent model for tracking product changes with comprehensive audit trail, translations, and business logic.
 *
 * @property int                             $id
 * @property int                             $product_id
 * @property int|null                        $user_id
 * @property string                          $action
 * @property string|null                     $field_name
 * @property mixed                           $old_value
 * @property mixed                           $new_value
 * @property string|null                     $description
 * @property string|null                     $ip_address
 * @property string|null                     $user_agent
 * @property array<string, mixed>|null       $metadata
 * @property string|null                     $causer_type
 * @property int|null                        $causer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $formatted_old_value
 * @property-read string $formatted_new_value
 * @property-read string $action_display
 * @property-read string $field_display
 * @property-read string $change_summary
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User|null $user
 * @property-read \Illuminate\Database\Eloquent\Model|Eloquent|null $causer
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory forProduct(int $productId)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory byAction(string $action)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory byField(string $fieldName)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistory withTranslations(?string $locale = null)
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class ProductHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ProductHistoryFactory> */
    use HasFactory;

    use HasTranslations;
    use LogsActivity;

    protected $fillable = ['product_id', 'user_id', 'action', 'field_name', 'old_value', 'new_value', 'description', 'ip_address', 'user_agent', 'metadata', 'causer_type', 'causer_id', 'created_at', 'updated_at'];

    protected $casts = [
        'metadata'   => 'array',
        'old_value'  => 'json',
        'new_value'  => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'product_histories';

    protected string $translationModel = \App\Models\Translations\ProductHistoryTranslation::class;

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            if (empty($model->causer_type)) {
                $model->causer_type = User::class;
            }
            if (empty($model->causer_id)) {
                $userId = auth()->id();
                $model->causer_id = is_int($userId) ? $userId : null;
            }
        });
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['action', 'field_name', 'description'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn (string $eventName) => "ProductHistory {$eventName}")->useLogName('product_history');
    }

    // Relations

    /**
     * Get the product that this history entry belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made this change.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the polymorphic causer of this change.
     *
     * @return MorphTo<Model, $this>
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    /**
     * Scope to filter history entries by product.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter history entries by user.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter history entries by action.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter history entries by field name.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeByField(Builder $query, string $fieldName): Builder
    {
        return $query->where('field_name', $fieldName);
    }

    /**
     * Scope to filter recent history entries within specified days.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
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
            'created'       => __('admin.product_history.actions.created'),
            'updated'       => __('admin.product_history.actions.updated'),
            'deleted'       => __('admin.product_history.actions.deleted'),
            'restored'      => __('admin.product_history.actions.restored'),
            'price_changed' => __('admin.product_history.actions.price_changed'),
            'stock_changed', 'stock_updated' => __('admin.product_history.actions.stock_updated'),
            'status_changed' => __('admin.product_history.actions.status_changed'),
            default          => $this->action,
        };
    }

    /**
     * Handle getFieldDisplayAttribute functionality with proper error handling.
     */
    public function getFieldDisplayAttribute(): string
    {
        return __('admin.product_history.fields.' . $this->field_name, [], $this->field_name);
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
     * Format a value for display.
     *
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        if (is_null($value)) {
            return __('admin.common.none');
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '[]';
        }
        if (is_bool($value)) {
            return $value ? __('admin.common.yes') : __('admin.common.no');
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '';
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
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public static function createHistoryEntry(Product $product, string $action, ?string $fieldName = null, $oldValue = null, $newValue = null, ?string $description = null, ?User $user = null): self
    {
        $userId = $user !== null ? $user->id : auth()->id();

        return self::create([
            'product_id'  => $product->id,
            'user_id'     => $userId,
            'action'      => $action,
            'field_name'  => $fieldName,
            'old_value'   => $oldValue,
            'new_value'   => $newValue,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => [
                'product_name' => $product->name,
                'product_sku'  => $product->sku,
                'timestamp'    => now()->toISOString(),
            ],
            'causer_type' => User::class,
            'causer_id'   => $userId,
        ]);
    }

    // Translation methods

    /**
     * Get translated action name.
     */
    public function getTranslatedAction(?string $locale = null): string
    {
        $translated = $this->trans('action', $locale);

        return is_string($translated) && $translated !== '' ? $translated : $this->action_display;
    }

    /**
     * Get translated description.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $translated = $this->trans('description', $locale);

        return is_string($translated) && $translated !== '' ? $translated : $this->description;
    }

    /**
     * Get translated field name.
     */
    public function getTranslatedFieldName(?string $locale = null): string
    {
        $translated = $this->trans('field_name', $locale);

        return is_string($translated) && $translated !== '' ? $translated : $this->field_display;
    }

    // Scope for translated histories

    /**
     * Scope to eager load translations for specific locale.
     *
     * @param  Builder<ProductHistory> $query
     * @return Builder<ProductHistory>
     */
    public function scopeWithTranslations(Builder $query, ?string $locale = null): Builder
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function (\Illuminate\Database\Eloquent\Relations\Relation $q) use ($locale): void {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this history entry

    /**
     * Get all available locales for this history entry.
     *
     * @return array<int, string>
     */
    public function getAvailableLocales(): array
    {
        /** @var array<int, string> */
        return $this->translations()->pluck('locale')->all();
    }

    // Check if history entry has translation for specific locale

    /**
     * Check if translation exists for specific locale.
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale

    /**
     * Get or create a translation record for the specified locale.
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\ProductHistoryTranslation
    {
        /** @var \App\Models\Translations\ProductHistoryTranslation $translation */
        $translation = $this->translations()->firstOrCreate(['locale' => $locale], ['action' => $this->action, 'description' => $this->description, 'field_name' => $this->field_name]);

        return $translation;
    }

    // Update translation for specific locale

    /**
     * Update translation for specific locale.
     *
     * @param array<string, mixed> $data
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
