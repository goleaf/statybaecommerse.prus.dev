<?php

declare(strict_types=1);

namespace App\Support\Notifications;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Resolve canonical notification category metadata for heterogeneous payloads.
 */
final class NotificationCategoryResolver
{
    /**
     * Attempt to resolve a category definition based on payload hints.
     *
     * @param  string|null                                                 $rawType           Legacy type string embedded inside the notification data payload.
     * @param  string|null                                                 $notificationClass Fully qualified notification class name stored on the model.
     * @return array{key: string, label: string, description: string}|null
     */
    public static function resolve(?string $rawType, ?string $notificationClass): ?array
    {
        // Normalise the payload hint so comparisons become deterministic.
        $normalizedType = self::normalizeKey($rawType);
        $categories = config('notifications.categories', []);

        // First attempt: directly match the provided type or alias.
        foreach ($categories as $key => $definition) {
            $aliases = self::aliasesFor($key, $definition);
            if ($normalizedType !== null && in_array($normalizedType, $aliases, true)) {
                return self::formatDefinition($key, $definition);
            }
        }

        // Second attempt: inspect the notification class name for a category keyword.
        $classKey = self::normalizeKey($notificationClass !== null ? class_basename($notificationClass) : null);
        if ($classKey !== null) {
            foreach ($categories as $key => $definition) {
                $aliases = self::aliasesFor($key, $definition);
                foreach ($aliases as $alias) {
                    if (Str::contains($classKey, $alias)) {
                        return self::formatDefinition($key, $definition);
                    }
                }
            }
        }

        // Fallback: when a raw type exists but no canonical category matches, keep a normalized echo.
        if ($normalizedType !== null) {
            return [
                'key'         => $normalizedType,
                'label'       => Str::headline(str_replace('_', ' ', $normalizedType)),
                'description' => '',
            ];
        }

        return null;
    }

    /**
     * Build the full alias list for a category definition.
     *
     * @param  array<string, mixed> $definition
     * @return array<int, string>
     */
    private static function aliasesFor(string $key, array $definition): array
    {
        $aliases = array_map(
            static fn (string $alias): string => self::normalizeKey($alias) ?? '',
            Arr::wrap($definition['aliases'] ?? [])
        );
        $aliases = array_filter($aliases, static fn (string $alias): bool => $alias !== '');
        // Always include the canonical key itself for direct comparisons.
        array_unshift($aliases, self::normalizeKey($key) ?? $key);

        return array_values(array_unique($aliases));
    }

    /**
     * Normalise keys into a snake_case value so alias comparisons work reliably.
     */
    private static function normalizeKey(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        return Str::of($trimmed)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', '_')
            ->trim('_')
            ->value();
    }

    /**
     * Shape the definition with sensible defaults for downstream consumers.
     *
     * @param  array<string, mixed>                                   $definition
     * @return array{key: string, label: string, description: string}
     */
    private static function formatDefinition(string $key, array $definition): array
    {
        return [
            'key'         => self::normalizeKey($key) ?? $key,
            'label'       => (string) ($definition['label'] ?? Str::headline($key)),
            'description' => (string) ($definition['description'] ?? ''),
        ];
    }
}
