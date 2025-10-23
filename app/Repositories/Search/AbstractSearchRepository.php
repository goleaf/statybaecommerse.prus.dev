<?php

declare(strict_types=1);

namespace App\Repositories\Search;

use App\Data\SearchQueryData;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class AbstractSearchRepository
{
    protected function connection(): ConnectionInterface
    {
        return DB::connection();
    }

    protected function driver(): string
    {
        return $this->connection()->getDriverName();
    }

    protected function supportsFullText(): bool
    {
        return in_array($this->driver(), ['mysql', 'mariadb'], true);
    }

    protected function likeOperator(): string
    {
        return $this->driver() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }

    protected function wildcardLower(string $term): string
    {
        return '%'.str_replace(['%', '_'], ['\\%', '\\_'], Str::lower($term)).'%';
    }

    protected function booleanFullTextTerm(string $term): string
    {
        $tokens = collect(preg_split('/\s+/u', trim($term)))
            ->filter()
            ->map(static fn (string $token): string => '+'.Str::lower($token).'*');

        return $tokens->isEmpty() ? Str::lower($term) : $tokens->implode(' ');
    }

    protected function applyPagination(Builder $builder, SearchQueryData $query, int $limit): Builder
    {
        $offset = ($query->page() - 1) * $limit;

        return $builder->offset($offset)->limit($limit);
    }

    protected function applySort(Builder $builder, SearchQueryData $query): Builder
    {
        return match ($query->sort()) {
            'price' => $builder->orderBy('price', 'asc'),
            'date' => $builder->orderByDesc('published_at'),
            default => $builder->orderByDesc('total_score'),
        };
    }
}
