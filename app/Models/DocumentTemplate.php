<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
/**
 * DocumentTemplate
 * 
 * Eloquent model representing the DocumentTemplate entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentTemplate query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class DocumentTemplate extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'description', 'content', 'variables', 'type', 'category', 'settings', 'is_active'];
    protected $casts = ['variables' => 'array', 'settings' => 'array', 'is_active' => 'boolean'];
    /**
     * Boot the service provider or trait functionality.
     * @return void
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
     * @return HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    /**
     * Handle getAvailableVariables functionality with proper error handling.
     * @return array
     */
    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }
    /**
     * Handle hasVariable functionality with proper error handling.
     * @param string $variable
     * @return bool
     */
    public function hasVariable(string $variable): bool
    {
        return in_array($variable, $this->getAvailableVariables());
    }
    /**
     * Handle getSettings functionality with proper error handling.
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }
    /**
     * Handle getSetting functionality with proper error handling.
     * @param string $key
     * @param mixed $default
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->getSettings()[$key] ?? $default;
    }
    /**
     * Render the Livewire component view with current state.
     * @param array $variables
     * @return string
     */
    public function render(array $variables = []): string
    {
        $content = $this->content;
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) $value, $content);
        }
        return $content;
    }
    /**
     * Handle scopeOfType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeOfCategory functionality with proper error handling.
     * @param mixed $query
     * @param string $category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
    /**
     * Handle getPrintSettings functionality with proper error handling.
     * @return array
     */
    public function getPrintSettings(): array
    {
        return $this->settings ?? ['header' => null, 'footer' => null, 'css' => null, 'page_size' => 'A4', 'orientation' => 'portrait', 'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20]];
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
     * Handle scopeByType functionality with proper error handling.
     * @param mixed $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
    /**
     * Handle scopeByCategory functionality with proper error handling.
     * @param mixed $query
     * @param string $category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}