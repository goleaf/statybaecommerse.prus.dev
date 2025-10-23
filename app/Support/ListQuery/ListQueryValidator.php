<?php

declare(strict_types=1);

namespace App\Support\ListQuery;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ListQueryValidator
{
    public static function fromRequest(Request $request, ListQueryDefinition $definition): ListQuery
    {
        $validated = Validator::make($request->query(), [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1'],
            'sort' => ['sometimes', 'string'],
            'filter' => ['sometimes', 'array'],
        ])->validate();

        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? $definition->defaultPerPage);
        $perPage = min($perPage, $definition->maxPerPage);

        $sortInput = $validated['sort'] ?? $definition->defaultSort.':'.$definition->defaultDirection;
        [$sortField, $sortDirection] = array_pad(explode(':', (string) $sortInput, 2), 2, null);

        $sortField = $sortField ?: $definition->defaultSort;
        $sortDirection = strtolower((string) ($sortDirection ?? $definition->defaultDirection));

        if (! array_key_exists($sortField, $definition->allowedSorts)) {
            throw ValidationException::withMessages([
                'sort' => __('validation.in', ['attribute' => 'sort']),
            ]);
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            throw ValidationException::withMessages([
                'sort' => __('validation.in', ['attribute' => 'sort direction']),
            ]);
        }

        /** @var array<string,mixed> $filters */
        $filters = Arr::where($validated['filter'] ?? [], static fn ($value) => $value !== null && $value !== '');

        return new ListQuery(
            page: $page,
            perPage: $perPage,
            sortField: $sortField,
            sortDirection: $sortDirection,
            filters: $filters,
        );
    }
}
