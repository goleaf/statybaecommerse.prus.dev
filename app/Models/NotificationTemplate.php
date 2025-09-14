<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
/**
 * NotificationTemplate
 * 
 * Eloquent model representing the NotificationTemplate entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplate query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NotificationTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'event', 'subject', 'content', 'variables', 'is_active'];
    protected $casts = ['subject' => 'json', 'content' => 'json', 'variables' => 'json', 'is_active' => 'boolean'];
    /**
     * Handle getLocalizedSubject functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getLocalizedSubject(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->subject[$locale] ?? $this->subject[config('app.fallback_locale')] ?? null;
    }
    /**
     * Handle getLocalizedContent functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getLocalizedContent(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->content[$locale] ?? $this->content[config('app.fallback_locale')] ?? null;
    }
    /**
     * Handle renderSubject functionality with proper error handling.
     * @param array $variables
     * @param string|null $locale
     * @return string
     */
    public function renderSubject(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedSubject($locale) ?? '';
        return $this->replaceVariables($template, $variables);
    }
    /**
     * Handle renderContent functionality with proper error handling.
     * @param array $variables
     * @param string|null $locale
     * @return string
     */
    public function renderContent(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedContent($locale) ?? '';
        return $this->replaceVariables($template, $variables);
    }
    /**
     * Handle replaceVariables functionality with proper error handling.
     * @param string $template
     * @param array $variables
     * @return string
     */
    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", (string) $value, $template);
        }
        return $template;
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
     * Handle getByEvent functionality with proper error handling.
     * @param string $event
     * @return self|null
     */
    public static function getByEvent(string $event): ?self
    {
        return self::where('event', $event)->where('is_active', true)->first();
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
     * Handle scopeByEvent functionality with proper error handling.
     * @param mixed $query
     * @param string $event
     */
    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}