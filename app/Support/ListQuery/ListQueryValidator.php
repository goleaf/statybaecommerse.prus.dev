<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ListQueryValidator
{
    public static function fromRequest(Request $request, ListQueryDefinition $definition): ListQuery
    {
        $rules = self::baseRules($definition);
        $filters = $definition->getFilters();

        foreach ($filters as $name => $filter) {
            $rules[$name] = self::ruleForFilter($filter);
        }

        $validated = Validator::make($request->query(), $rules)->validate();

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? $definition->getDefaultPerPage());
        $perPage = max(1, min($perPage, $definition->getMaxPerPage()));

        $sortBy = (string) ($validated['sort_by'] ?? $definition->getDefaultSortBy());
        $sortDirection = strtolower((string) ($validated['sort_dir'] ?? $definition->getDefaultSortDirection()));

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = $definition->getDefaultSortDirection();
        }

        $filterValues = [];

        foreach ($filters as $name => $filter) {
            if (! array_key_exists($name, $validated)) {
                $filterValues[$name] = null;

                continue;
            }

            $filterValues[$name] = self::castFilterValue($validated[$name], $filter);
        }

        return new ListQuery($page, $perPage, $sortBy, $sortDirection, $filterValues);
    }

    private static function baseRules(ListQueryDefinition $definition): array
    {
        $sortKeys = array_keys($definition->getSorts());
        $rules = [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.$definition->getMaxPerPage()],
            'sort_by' => ['sometimes', 'string'],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];

        if ($sortKeys !== []) {
            $rules['sort_by'][] = Rule::in($sortKeys);
        }

        return $rules;
    }

    /**
     * @param array{column?: string, type?: string, operator?: string, callback?: \Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string} $filter
     */
    private static function ruleForFilter(array $filter): array
    {
        $rules = ['sometimes'];

        if (($filter['nullable'] ?? false) === true) {
            $rules[] = 'nullable';
        }

        $type = $filter['type'] ?? 'string';

        $rules[] = match ($type) {
            'int', 'integer' => 'integer',
            'numeric', 'float', 'decimal' => 'numeric',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            'date' => 'date',
            'datetime' => 'date',
            default => 'string',
        };

        if (isset($filter['allowed']) && is_array($filter['allowed']) && $filter['allowed'] !== []) {
            $rules[] = Rule::in($filter['allowed']);
        }

        if (isset($filter['enum'])) {
            $rules[] = Rule::enum($filter['enum']);
        }

        return $rules;
    }

    /**
     * @param array{column?: string, type?: string, operator?: string, callback?: \Closure, scope?: string, nullable?: bool, allowed?: array<int, string|int|float>, enum?: class-string} $filter
     */
    private static function castFilterValue(mixed $value, array $filter): mixed
    {
        if ($value === null) {
            return null;
        }

        $type = $filter['type'] ?? 'string';

        return match ($type) {
            'int', 'integer' => (int) $value,
            'numeric', 'float', 'decimal' => (float) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'array' => is_array($value) ? $value : Arr::wrap($value),
            default => is_string($value) ? trim($value) : $value,
        };
    }
}
