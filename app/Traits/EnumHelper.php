<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Collection;

/**
 * EnumHelper
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait EnumHelper
{
    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum labels as an array
     */
    public static function labels(): array
    {
        return collect(self::cases())->map(fn ($case) => $case->label())->toArray();
    }

    /**
     * Get enum options for select dropdowns
     */
    public static function options(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }

    /**
     * Get enum options with full metadata for advanced UI components
     */
    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())->sortBy('priority')->mapWithKeys(fn ($case) => [$case->value => $case->toArray()])->toArray();
    }

    /**
     * Get enum cases as a collection
     */
    public static function collection(): Collection
    {
        return collect(self::cases());
    }

    /**
     * Get ordered enum cases by priority
     */
    public static function ordered(): Collection
    {
        return collect(self::cases())->sortBy('priority');
    }

    /**
     * Find enum case by label
     */
    public static function fromLabel(string $label): ?static
    {
        return collect(self::cases())->first(fn ($case) => $case->label() === $label);
    }

    /**
     * Check if enum has a specific value
     */
    public static function hasValue(string $value): bool
    {
        return in_array($value, self::values());
    }

    /**
     * Get random enum case
     */
    public static function random(): static
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }

    /**
     * Get first enum case
     */
    public static function first(): static
    {
        return self::cases()[0];
    }

    /**
     * Get last enum case
     */
    public static function last(): static
    {
        $cases = self::cases();

        return end($cases);
    }

    /**
     * Get enum case by index
     */
    public static function at(int $index): ?static
    {
        $cases = self::cases();

        return $cases[$index] ?? null;
    }

    /**
     * Get enum count
     */
    public static function count(): int
    {
        return count(self::cases());
    }

    /**
     * Check if enum is empty
     */
    public static function isEmpty(): bool
    {
        return empty(self::cases());
    }

    /**
     * Get enum cases as JSON string
     */
    public static function toJson(): string
    {
        return json_encode(self::optionsWithDescriptions());
    }

    /**
     * Get enum cases as array with custom mapping
     */
    public static function map(callable $callback): array
    {
        return collect(self::cases())->map($callback)->toArray();
    }

    /**
     * Filter enum cases with custom callback
     */
    public static function filter(callable $callback): Collection
    {
        return collect(self::cases())->filter($callback);
    }

    /**
     * Get enum cases grouped by a property
     */
    public static function groupBy(string $property): Collection
    {
        return collect(self::cases())->groupBy($property);
    }

    /**
     * Get enum cases sorted by a property
     */
    public static function sortBy(string $property, bool $descending = false): Collection
    {
        $collection = collect(self::cases())->sortBy($property);

        return $descending ? $collection->reverse() : $collection;
    }

    /**
     * Get enum cases with pagination
     */
    public static function paginate(int $perPage, int $page = 1): array
    {
        $cases = collect(self::cases());
        $offset = ($page - 1) * $perPage;

        return ['data' => $cases->slice($offset, $perPage)->values()->toArray(), 'current_page' => $page, 'per_page' => $perPage, 'total' => $cases->count(), 'last_page' => ceil($cases->count() / $perPage), 'from' => $offset + 1, 'to' => min($offset + $perPage, $cases->count())];
    }

    /**
     * Search enum cases by label or value
     */
    public static function search(string $query): Collection
    {
        return collect(self::cases())->filter(function ($case) use ($query) {
            $query = strtolower($query);

            return str_contains(strtolower($case->value), $query) || str_contains(strtolower($case->label()), $query);
        });
    }

    /**
     * Get enum cases as key-value pairs for API responses
     */
    public static function forApi(): array
    {
        return collect(self::cases())->map(fn ($case) => ['value' => $case->value, 'label' => $case->label(), 'description' => method_exists($case, 'description') ? $case->description() : null, 'icon' => method_exists($case, 'icon') ? $case->icon() : null, 'color' => method_exists($case, 'color') ? $case->color() : null])->toArray();
    }

    /**
     * Get enum cases for form validation rules
     */
    public static function forValidation(): string
    {
        return 'in:'.implode(',', self::values());
    }

    /**
     * Get enum cases for database enum column
     */
    public static function forDatabase(): string
    {
        return "enum('".implode("','", self::values())."')";
    }

    /**
     * Get enum cases for GraphQL enum type
     */
    public static function forGraphQL(): array
    {
        return collect(self::cases())->map(fn ($case) => ['name' => strtoupper($case->value), 'value' => $case->value, 'description' => method_exists($case, 'description') ? $case->description() : null])->toArray();
    }

    /**
     * Get enum cases for TypeScript enum
     */
    public static function forTypeScript(): string
    {
        $enum = 'export enum '.class_basename(static::class)." {\n";
        foreach (self::cases() as $case) {
            $enum .= '  '.strtoupper($case->value)." = '".$case->value."',\n";
        }

        return $enum.'}';
    }

    /**
     * Get enum cases for JavaScript object
     */
    public static function forJavaScript(): string
    {
        $object = 'const '.class_basename(static::class)." = {\n";
        foreach (self::cases() as $case) {
            $object .= '  '.strtoupper($case->value).": '".$case->value."',\n";
        }

        return $object.'};';
    }

    /**
     * Get enum cases for CSS custom properties
     */
    public static function forCss(): string
    {
        $css = ":root {\n";
        foreach (self::cases() as $case) {
            $css .= '  --'.str_replace('_', '-', $case->value).": '".$case->value."';\n";
        }

        return $css.'}';
    }

    /**
     * Get enum cases for documentation
     */
    public static function forDocumentation(): array
    {
        return collect(self::cases())->map(fn ($case) => ['value' => $case->value, 'label' => $case->label(), 'description' => method_exists($case, 'description') ? $case->description() : null, 'icon' => method_exists($case, 'icon') ? $case->icon() : null, 'color' => method_exists($case, 'color') ? $case->color() : null, 'priority' => method_exists($case, 'priority') ? $case->priority() : null])->toArray();
    }

    /**
     * Get enum statistics
     */
    public static function statistics(): array
    {
        $cases = collect(self::cases());

        return ['total' => $cases->count(), 'has_descriptions' => $cases->every(fn ($case) => method_exists($case, 'description')), 'has_icons' => $cases->every(fn ($case) => method_exists($case, 'icon')), 'has_colors' => $cases->every(fn ($case) => method_exists($case, 'color')), 'has_priority' => $cases->every(fn ($case) => method_exists($case, 'priority')), 'average_priority' => $cases->every(fn ($case) => method_exists($case, 'priority')) ? $cases->avg('priority') : null];
    }
}
