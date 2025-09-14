<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

final /**
 * SystemSettingCategory
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class SystemSettingCategory extends Model
{
    use HasFactory, HasSlug, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsWhenUpdating();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'description', 'is_active', 'sort_order'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "System Setting Category {$eventName}")
            ->useLogName('system_setting_categories');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(SystemSetting::class, 'category_id');
    }

    public function parent(): HasMany
    {
        return $this->hasMany(SystemSettingCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(SystemSettingCategory::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SystemSettingCategoryTranslation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithSettings($query)
    {
        return $query->with(['settings' => function ($q) {
            $q->active()->ordered();
        }]);
    }

    public function getTranslatedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->name ?? $this->name;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->description ?? $this->description;
    }

    public function getSettingsCount(): int
    {
        return $this->settings()->active()->count();
    }

    public function hasActiveSettings(): bool
    {
        return $this->settings()->active()->exists();
    }

    public function getIconClass(): string
    {
        return $this->icon ?? 'heroicon-o-cog-6-tooth';
    }

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

    public function getActiveSettingsCount(): int
    {
        return $this->settings()->active()->count();
    }

    public function getPublicSettingsCount(): int
    {
        return $this->settings()->active()->public()->count();
    }

    public function getSettingsByGroup(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->settings()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('group');
    }

    public function hasPublicSettings(): bool
    {
        return $this->settings()->active()->public()->exists();
    }

    public function getParent(): ?self
    {
        return $this->parent()->first();
    }

    public function getChildren(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->children()->active()->ordered()->get();
    }

    public function getAllChildren(): \Illuminate\Database\Eloquent\Collection
    {
        $children = collect();

        foreach ($this->getChildren() as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }

        return $children;
    }

    public function getPath(): string
    {
        $path = [$this->getTranslatedName()];
        $parent = $this->getParent();

        while ($parent) {
            array_unshift($path, $parent->getTranslatedName());
            $parent = $parent->getParent();
        }

        return implode(' > ', $path);
    }

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

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return $this->getChildren()->isEmpty();
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;

        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->getTranslatedName(),
                'slug' => $current->slug,
            ]);
            $current = $current->getParent();
        }

        return $breadcrumb;
    }

    public function getTreeStructure(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslatedName(),
            'slug' => $this->slug,
            'description' => $this->getTranslatedDescription(),
            'icon' => $this->getIconClass(),
            'color' => $this->color,
            'settings_count' => $this->getActiveSettingsCount(),
            'public_settings_count' => $this->getPublicSettingsCount(),
            'children' => $this->getChildren()->map->getTreeStructure(),
        ];
    }
}
