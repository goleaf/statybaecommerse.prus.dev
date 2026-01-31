<?php

declare(strict_types=1);

namespace App\Support\Filament\Components;

use Closure;
use Filament\Forms\Components\Select;

class SearchableInput extends Select
{
    /**
     * @var array<string, mixed>
     */
    protected array $payload = [];

    /**
     * @var array<string, mixed>
     */
    protected array $meta = [];

    /**
     * @var array<int, array{value: string, label: string, data: array<string, mixed>}>
     */
    protected array $lastSearchResults = [];

    public function payload(array $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function meta(string $key, mixed $value): static
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function getMeta(array|string|null $keys = null): mixed
    {
        if ($keys === null) {
            return $this->meta;
        }

        if (is_array($keys)) {
            $values = [];

            foreach ($keys as $key) {
                $values[$key] = $this->meta[$key] ?? null;
            }

            return $values;
        }

        return $this->meta[$keys] ?? null;
    }

    public function getSearchResultsUsing(?Closure $callback): static
    {
        return parent::getSearchResultsUsing($callback);
    }

    public function searchUsing(?Closure $callback): static
    {
        return $this->getSearchResultsUsing($callback);
    }

    public function getSearchResults(string $search): array
    {
        $results = parent::getSearchResults($search);
        $this->lastSearchResults = [];

        if ($results === []) {
            return $results;
        }

        if (array_is_list($results) && $results[0] instanceof \App\Support\Search\SearchResult) {
            $this->lastSearchResults = array_map(
                static fn (\App\Support\Search\SearchResult $result): array => $result->toArray(),
                $results,
            );

            return array_reduce(
                $results,
                static function (array $options, \App\Support\Search\SearchResult $result): array {
                    $options[$result->value()] = $result->label();

                    return $options;
                },
                [],
            );
        }

        if (array_is_list($results) && is_array($results[0]) && array_key_exists('value', $results[0])) {
            /** @var array<int, array{value: string, label: string, data?: array<string, mixed>}> $results */
            $this->lastSearchResults = $results;

            return array_reduce(
                $results,
                static function (array $options, array $result): array {
                    $options[(string) $result['value']] = (string) $result['label'];

                    return $options;
                },
                [],
            );
        }

        return $results;
    }

    public function getSearchResultsForJs(string $search): array
    {
        $options = $this->getSearchResults($search);

        if ($this->lastSearchResults !== []) {
            return array_map(static function (array $result): array {
                return [
                    'label' => (string) ($result['label'] ?? ''),
                    'value' => (string) ($result['value'] ?? ''),
                    'data'  => is_array($result['data'] ?? null) ? $result['data'] : [],
                ];
            }, $this->lastSearchResults);
        }

        return parent::getSearchResultsForJs($search);
    }

    /**
     * Helper to satisfy legacy searchable input method signature.
     */
    public function hydrateStateUsing(?callable $callback): static
    {
        return $this->afterStateHydrated($callback);
    }
}
