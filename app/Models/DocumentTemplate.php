<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Database\Factories\DocumentTemplateFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Stringable;

/**
 * DocumentTemplate
 *
 * Eloquent model representing the DocumentTemplate entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class DocumentTemplate extends Model
{
    /** @use HasFactory<DocumentTemplateFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'content', 'variables', 'type', 'category', 'settings', 'is_active'];

    protected $casts = ['variables' => 'array', 'settings' => 'array', 'is_active' => 'boolean'];

    /**
     * Boot the service provider or trait functionality.
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function (DocumentTemplate $template): void {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
        self::updating(function (DocumentTemplate $template): void {
            if ($template->isDirty('name') && empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Handle documents functionality with proper error handling.
     *
     * @return HasMany<Document, DocumentTemplate>
     */
    public function documents(): HasMany
    {
        /** @var HasMany<Document, DocumentTemplate> $relation */
        $relation = $this->hasMany(Document::class);

        // Bypass all global scopes to surface drafts and archived documents linked to the template.
        return $relation->withoutGlobalScopes();
    }

    /**
     * Handle getAvailableVariables functionality with proper error handling.
     *
     * @return array<int|string, mixed>
     */
    public function getAvailableVariables(): array
    {
        return is_array($this->variables) ? $this->variables : [];
    }

    /**
     * Handle hasVariable functionality with proper error handling.
     */
    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->getAvailableVariables());
    }

    /**
     * Handle getSettings functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return is_array($this->settings) ? $this->settings : [];
    }

    /**
     * Handle getSetting functionality with proper error handling.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->getSettings()[$key] ?? $default;
    }

    /**
     * Render the Livewire component view with current state.
     *
     * @param array<string, scalar|Stringable|null> $variables
     */
    public function render(array $variables = []): string
    {
        $content = $this->content;
        foreach ($variables as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (! is_scalar($value) && ! $value instanceof Stringable) {
                continue;
            }

            $placeholder = '{{' . (string) $key . '}}';
            $content = str_replace($placeholder, (string) $value, $content);
        }

        return $content;
    }

    /**
     * Handle scopeOfType functionality with proper error handling.
     *
     * @param Builder<DocumentTemplate> $query
     */
    /**
     * @return Builder<DocumentTemplate>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeOfCategory functionality with proper error handling.
     *
     * @param Builder<DocumentTemplate> $query
     */
    /**
     * @return Builder<DocumentTemplate>
     */
    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Handle getPrintSettings functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function getPrintSettings(): array
    {
        $defaults = [
            'header'      => null,
            'footer'      => null,
            'css'         => null,
            'page_size'   => 'A4',
            'orientation' => 'portrait',
            'margins'     => [
                'top'    => 20,
                'right'  => 20,
                'bottom' => 20,
                'left'   => 20,
            ],
        ];

        $settings = is_array($this->settings) ? $this->settings : [];

        // Merge persisted settings with sensible defaults to avoid missing keys in downstream consumers.
        /** @var array<string, mixed> $merged */
        $merged = array_replace_recursive($defaults, $settings);

        return $merged;
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param Builder<DocumentTemplate> $query
     */
    /**
     * @return Builder<DocumentTemplate>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param Builder<DocumentTemplate> $query
     */
    /**
     * @return Builder<DocumentTemplate>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     *
     * @param Builder<DocumentTemplate> $query
     */
    /**
     * @return Builder<DocumentTemplate>
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to order templates alphabetically by their human readable name.
     *
     * @param  Builder<DocumentTemplate> $query
     * @return Builder<DocumentTemplate>
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Order by the user-facing "name" column so dropdowns remain predictable.
        return $query->orderBy('name');
    }
}
