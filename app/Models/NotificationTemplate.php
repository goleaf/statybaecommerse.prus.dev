<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stringable;

/**
 * @property string                     $name
 * @property string                     $slug
 * @property string                     $type
 * @property string                     $event
 * @property array<string, string>|null $subject
 * @property array<string, string>|null $content
 * @property array<int, string>|null    $variables
 * @property bool                       $is_active
 *
 * @method static Builder|NotificationTemplate active()
 * @method static Builder|NotificationTemplate byEvent(string $event)
 * @method static Builder|NotificationTemplate byType(string $type)
 * @method static Builder|NotificationTemplate orderedByName()
 * @method static Builder|NotificationTemplate newModelQuery()
 * @method static Builder|NotificationTemplate newQuery()
 * @method static Builder|NotificationTemplate query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NotificationTemplate extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * @var array<int, string>
     */
    protected $fillable = ['name', 'slug', 'type', 'event', 'subject', 'content', 'variables', 'is_active'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'subject'   => 'array',
        'content'   => 'array',
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Handle getLocalizedSubject functionality with proper error handling.
     */
    public function getLocalizedSubject(?string $locale = null): ?string
    {
        // Resolve the preferred locale by falling back to the application default when needed.
        $locale = $locale !== null && $locale !== '' ? $locale : app()->getLocale();

        $subjects = is_array($this->subject) ? $this->subject : [];

        $candidates = array_values(array_filter([
            $locale,
            config('app.fallback_locale'),
        ], static fn (?string $candidate): bool => is_string($candidate) && $candidate !== ''));

        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $subjects) && is_string($subjects[$candidate]) && $subjects[$candidate] !== '') {
                return $subjects[$candidate];
            }
        }

        return null;
    }

    /**
     * Handle getLocalizedContent functionality with proper error handling.
     */
    public function getLocalizedContent(?string $locale = null): ?string
    {
        // Resolve the preferred locale by falling back to the application default when needed.
        $locale = $locale !== null && $locale !== '' ? $locale : app()->getLocale();

        $content = is_array($this->content) ? $this->content : [];

        $candidates = array_values(array_filter([
            $locale,
            config('app.fallback_locale'),
        ], static fn (?string $candidate): bool => is_string($candidate) && $candidate !== ''));

        foreach ($candidates as $candidate) {
            if (array_key_exists($candidate, $content) && is_string($content[$candidate]) && $content[$candidate] !== '') {
                return $content[$candidate];
            }
        }

        return null;
    }

    /**
     * Handle renderSubject functionality with proper error handling.
     */
    public function renderSubject(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedSubject($locale) ?? '';

        return $this->replaceVariables($template, $variables);
    }

    /**
     * Handle renderContent functionality with proper error handling.
     */
    public function renderContent(array $variables = [], ?string $locale = null): string
    {
        $template = $this->getLocalizedContent($locale) ?? '';

        return $this->replaceVariables($template, $variables);
    }

    /**
     * Handle replaceVariables functionality with proper error handling.
     */
    private function replaceVariables(string $template, array $variables): string
    {
        // Iterate through the provided replacements, ensuring we only process string keys.
        foreach ($variables as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $replacement = $value instanceof Stringable ? (string) $value : (is_scalar($value) ? (string) $value : '');

            $template = str_replace("{{$key}}", $replacement, $template);
        }

        return $template;
    }

    /**
     * Handle getAvailableVariables functionality with proper error handling.
     */
    public function getAvailableVariables(): array
    {
        $rawVariables = $this->variables;

        if ($rawVariables === null) {
            return [];
        }

        $candidates = [];

        if (is_string($rawVariables)) {
            $candidates = explode(',', $rawVariables);
        }

        if (is_array($rawVariables)) {
            $candidates = $rawVariables;
        }

        $normalized = [];

        foreach ($candidates as $candidate) {
            if ($candidate instanceof Stringable) {
                $normalized[] = trim((string) $candidate);

                continue;
            }

            if (is_scalar($candidate)) {
                $normalized[] = trim((string) $candidate);
            }
        }

        $normalized = array_values(array_filter($normalized, static fn (string $value): bool => $value !== ''));

        return array_values(array_unique($normalized));
    }

    /**
     * Handle getByEvent functionality with proper error handling.
     */
    public static function getByEvent(string $event): ?self
    {
        // Prefer fluent query builders to keep the intent of the lookup explicit.
        return self::query()
            ->where('event', $event)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeActive(Builder $query): Builder
    {
        // Limit the query to templates that are currently enabled for delivery.
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        // Filter templates by the delivery channel, such as email or SMS.
        return $query->where('type', $type);
    }

    /**
     * Handle scopeByEvent functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByEvent(Builder $query, string $event): Builder
    {
        // Narrow down templates by the domain event that triggers them.
        return $query->where('event', $event);
    }
}
