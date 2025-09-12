<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class NotificationTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'event',
        'subject',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'subject' => 'json',
        'content' => 'json',
        'variables' => 'json',
        'is_active' => 'boolean',
    ];

    public function getLocalizedSubject(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->subject[$locale] ?? $this->subject[config('app.fallback_locale')] ?? null;
    }

    public function getLocalizedContent(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        return $this->content[$locale] ?? $this->content[config('app.fallback_locale')] ?? null;
    }

    public function renderSubject(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedSubject($locale) ?? '';

        return $this->replaceVariables($template, $variables);
    }

    public function renderContent(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedContent($locale) ?? '';

        return $this->replaceVariables($template, $variables);
    }

    private function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace("{{$key}}", (string) $value, $template);
        }

        return $template;
    }

    public function getAvailableVariables(): array
    {
        return $this->variables ?? [];
    }

    public static function getByEvent(string $event): ?self
    {
        return self::where('event', $event)
            ->where('is_active', true)
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
