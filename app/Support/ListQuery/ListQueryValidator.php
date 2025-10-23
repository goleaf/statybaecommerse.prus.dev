<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class ListQueryValidator
{
    public static function fromRequest(Request $request, ListQueryDefinition $definition): ListQuery
    {
        return self::fromArray($request->query(), $definition);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input, ListQueryDefinition $definition): ListQuery
    {
        $page = max(1, (int) ($input['page'] ?? 1));
        $perPage = self::resolvePerPage($input['per_page'] ?? null, $definition);

        [$filterDefinitions, $filters] = self::prepareFilters($input, $definition);
        [$sortDefinitions, $sorts] = self::prepareSorts($input, $definition);

        return new ListQuery($page, $perPage, $filterDefinitions, $sortDefinitions, $filters, $sorts);
    }

    private static function resolvePerPage(mixed $value, ListQueryDefinition $definition): int
    {
        $perPage = $definition->defaultPerPage();

        if ($value !== null && $value !== '') {
            $perPage = (int) $value;
        }

        if ($perPage < $definition->minPerPage()) {
            $perPage = $definition->defaultPerPage();
        }

        if ($perPage > $definition->maxPerPage()) {
            $perPage = $definition->maxPerPage();
        }

        return $perPage;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: array<int, array{key: string, value: mixed, column: string|null, operator: string, callback: (callable)|null}>, 1: array<string, mixed>}
     */
    private static function prepareFilters(array $input, ListQueryDefinition $definition): array
    {
        $filters = [];
        $filterDefinitions = [];

        foreach ($definition->filters() as $key => $config) {
            $value = self::extractFilterValue($input, $key);
            $hasExplicitValue = array_key_exists($key, $input)
                || Arr::has($input, "filters.$key")
                || (str_contains($key, '.') && Arr::has($input, $key));

            if (! $hasExplicitValue && $value === null) {
                continue;
            }

            $casted = self::castValue($value, $config['type'] ?? 'string', (bool) ($config['allow_empty'] ?? false));

            if ($casted === null && ! ($config['nullable'] ?? false)) {
                continue;
            }

            if (($config['allowed'] ?? null) !== null && ! in_array($casted, $config['allowed'], true)) {
                continue;
            }

            if ($casted === null) {
                continue;
            }

            $filters[$key] = $casted;
            $filterDefinitions[] = [
                'key' => $key,
                'value' => $casted,
                'column' => $config['column'] ?? null,
                'operator' => $config['operator'] ?? '=',
                'callback' => $config['callback'] ?? null,
            ];
        }

        return [$filterDefinitions, $filters];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{0: array<int, array{key: string, column: string, direction: string}>, 1: array<int, array{key: string, direction: string}>}
     */
    private static function prepareSorts(array $input, ListQueryDefinition $definition): array
    {
        $sortable = $definition->sortable();
        $requested = self::extractSorts($input);
        $sortDefinitions = [];
        $sorts = [];

        foreach ($requested as $sortKey => $direction) {
            if (! isset($sortable[$sortKey])) {
                continue;
            }

            $column = $sortable[$sortKey]['column'];
            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

            $sortDefinitions[] = [
                'key' => $sortKey,
                'column' => $column,
                'direction' => $direction,
            ];

            $sorts[] = [
                'key' => $sortKey,
                'direction' => $direction,
            ];
        }

        if ($sortDefinitions === [] && ($default = $definition->defaultSort()) !== null && isset($sortable[$default])) {
            $direction = $sortable[$default]['default_direction'] ?? $definition->defaultDirection();
            $direction = strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';

            $sortDefinitions[] = [
                'key' => $default,
                'column' => $sortable[$default]['column'],
                'direction' => $direction,
            ];

            $sorts[] = [
                'key' => $default,
                'direction' => $direction,
            ];
        }

        return [$sortDefinitions, $sorts];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    private static function extractSorts(array $input): array
    {
        $sorts = [];

        $rawSort = $input['sort'] ?? null;
        if (is_string($rawSort) && $rawSort !== '') {
            $segments = array_filter(array_map('trim', explode(',', $rawSort)));
            foreach ($segments as $segment) {
                $direction = Str::startsWith($segment, '-') ? 'desc' : 'asc';
                $key = ltrim($segment, '-');
                $sorts[$key] = $direction;
            }
        }

        if ($sorts === [] && isset($input['sort_by'])) {
            $direction = strtolower((string) ($input['sort_direction'] ?? $input['sort_order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
            $sorts[(string) $input['sort_by']] = $direction;
        }

        return $sorts;
    }

    private static function extractFilterValue(array $input, string $key): mixed
    {
        if (array_key_exists($key, $input)) {
            return $input[$key];
        }

        if (str_contains($key, '.')) {
            return Arr::get($input, $key);
        }

        return Arr::get($input, "filters.$key");
    }

    private static function castValue(mixed $value, string $type, bool $allowEmpty): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int', 'integer' => self::castInteger($value),
            'bool', 'boolean' => self::castBoolean($value),
            'date' => self::castDate($value, false),
            'datetime' => self::castDate($value, true),
            'array' => is_array($value) ? $value : null,
            default => self::castString($value, $allowEmpty),
        };
    }

    private static function castInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value) && (int) $value == $value) {
            return (int) $value;
        }

        return null;
    }

    private static function castBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered;
    }

    private static function castDate(mixed $value, bool $withTime): ?string
    {
        if (! is_string($value) && ! $value instanceof \DateTimeInterface) {
            return null;
        }

        try {
            $date = $value instanceof \DateTimeInterface ? CarbonImmutable::instance($value) : new CarbonImmutable($value);
        } catch (\Throwable) {
            return null;
        }

        return $withTime ? $date->toDateTimeString() : $date->toDateString();
    }

    private static function castString(mixed $value, bool $allowEmpty): ?string
    {
        if (is_array($value)) {
            return null;
        }

        $string = trim((string) $value);

        if ($string === '' && ! $allowEmpty) {
            return null;
        }

        return $string;
    }
}
