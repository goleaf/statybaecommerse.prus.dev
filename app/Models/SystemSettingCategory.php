<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * SystemSettingCategory
 *
 * Eloquent model representing the SystemSettingCategory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSettingCategory query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class SystemSettingCategory extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'color', 'sort_order', 'is_active', 'parent_id'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer', 'parent_id' => 'integer'];

    /**
     * Handle getSlugOptions functionality with proper error handling.
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'slug', 'description', 'is_active', 'sort_order'])->logOnlyDirty()->dontSubmitEmptyLogs()->setDescriptionForEvent(fn(string $eventName) => "System Setting Category {$eventName}")->useLogName('system_setting_categories');
    }

    /**
     * Handle settings functionality with proper error handling.
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(SystemSetting::class, 'category_id');
    }

    /**
     * Handle parent functionality with proper error handling.
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SystemSettingCategory::class, 'parent_id');
    }

    /**
     * Handle children functionality with proper error handling.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SystemSettingCategory::class, 'parent_id');
    }

    /**
     * Handle translations functionality with proper error handling.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SystemSettingCategoryTranslation::class);
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Handle scopeRoot functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Handle scopeWithSettings functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithSettings($query)
    {
        return $query->with(['settings' => function ($q) {
            $q->active()->ordered();
        }]);
    }

    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->name ?? $this->name;
    }

    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->description ?? $this->description;
    }

    /**
     * Handle getSettingsCount functionality with proper error handling.
     * @return int
     */
    public function getSettingsCount(): int
    {
        return $this->settings()->count();
    }

    /**
     * Handle hasActiveSettings functionality with proper error handling.
     * @return bool
     */
    public function hasActiveSettings(): bool
    {
        return $this->settings()->active()->exists();
    }

    /**
     * Handle getIconClass functionality with proper error handling.
     * @return string
     */
    public function getIconClass(): string
    {
        return $this->icon ?? 'heroicon-o-cog-6-tooth';
    }

    /**
     * Handle getColorClass functionality with proper error handling.
     * @return string
     */
    public function getColorClass(): string
    {
        return match ($this->color) {
            'primary' => 'text-primary-600',
            'secondary' => 'text-secondary-600',
            'success' => 'text-success-600',
            'warning' => 'text-warning-600',
            'danger' => 'text-danger-600',
            'info' => 'text-info-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Handle getBadgeColorClass functionality with proper error handling.
     * @return string
     */
    public function getBadgeColorClass(): string
    {
        return match ($this->color) {
            'primary' => 'bg-primary-100 text-primary-800',
            'secondary' => 'bg-secondary-100 text-secondary-800',
            'success' => 'bg-success-100 text-success-800',
            'warning' => 'bg-warning-100 text-warning-800',
            'danger' => 'bg-danger-100 text-danger-800',
            'info' => 'bg-info-100 text-info-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Handle getActiveSettingsCount functionality with proper error handling.
     * @return int
     */
    public function getActiveSettingsCount(): int
    {
        return $this->settings()->active()->count();
    }

    /**
     * Handle getPublicSettingsCount functionality with proper error handling.
     * @return int
     */
    public function getPublicSettingsCount(): int
    {
        return $this->settings()->public()->count();
    }

    /**
     * Handle getSettingsByGroup functionality with proper error handling.
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getSettingsByGroup(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->settings()->ordered()->get()->groupBy('group');
    }

    /**
     * Handle hasPublicSettings functionality with proper error handling.
     * @return bool
     */
    public function hasPublicSettings(): bool
    {
        return $this->settings()->active()->public()->exists();
    }

    /**
     * Handle getParent functionality with proper error handling.
     * @return self|null
     */
    public function getParent(): ?self
    {
        return $this->parent;
    }

    /**
     * Handle getChildren functionality with proper error handling.
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getChildren(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->children()->active()->ordered()->get();
    }

    /**
     * Handle getAllChildren functionality with proper error handling.
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getAllChildren(): \Illuminate\Database\Eloquent\Collection
    {
        $children = $this->getChildren();
        foreach ($this->getChildren() as $child) {
            $children = $children->merge($child->getAllChildren());
        }
        return $children;
    }

    /**
     * Handle getPath functionality with proper error handling.
     * @return string
     */
    public function getPath(): string
    {
        $path = [$this->name];
        $parent = $this->getParent();
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->getParent();
        }
        return implode(' > ', $path);
    }

    /**
     * Handle getDepth functionality with proper error handling.
     * @return int
     */
    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->getParent();
        while ($parent) {
            $depth++;
            $parent = $parent->getParent();
        }
        return $depth;
    }

    /**
     * Handle isRoot functionality with proper error handling.
     * @return bool
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Handle isLeaf functionality with proper error handling.
     * @return bool
     */
    public function isLeaf(): bool
    {
        return $this->getChildren()->isEmpty();
    }

    /**
     * Handle getBreadcrumb functionality with proper error handling.
     * @return array
     */
    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;
        while ($current) {
            array_unshift($breadcrumb, ['id' => $current->id, 'name' => $current->getTranslatedName(), 'slug' => $current->slug]);
            $current = $current->getParent();
        }
        return $breadcrumb;
    }

    /**
     * Handle getTreeStructure functionality with proper error handling.
     * @return array
     */
    public function getTreeStructure(): array
    {
        return ['id' => $this->id, 'name' => $this->getTranslatedName(), 'slug' => $this->slug, 'description' => $this->getTranslatedDescription(), 'icon' => $this->getIconClass(), 'color' => $this->color, 'settings_count' => $this->getSettingsCount(), 'public_settings_count' => $this->getPublicSettingsCount(), 'children' => $this->getChildren()->map->getTreeStructure()];
    }
}
