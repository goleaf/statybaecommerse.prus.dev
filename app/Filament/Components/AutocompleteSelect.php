<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * AutocompleteSelect Component
 *
 * A custom Filament form component that provides autocomplete functionality
 * for select fields with search capabilities.
 */
final class AutocompleteSelect extends Select
{
    protected string $view = 'filament.components.autocomplete-select';

    protected bool $searchable = true;

    protected bool $multiple = false;

    protected int $minSearchLength = 2;

    protected int $maxSearchResults = 10;

    protected ?string $searchField = null;

    protected ?string $valueField = null;

    protected ?string $labelField = null;

    protected ?string $modelClass = null;

    protected ?Collection $searchResults = null;

    protected ?string $searchQuery = null;

    /**
     * Cached search results keyed by trimmed query string.
     *
     * @var array<string, Collection>
     */
    protected array $searchCache = [];

    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function searchable(Closure|array|bool $condition = true): static
    {
        parent::searchable($condition);

        $this->searchable = (bool) $this->evaluate($condition);

        return $this;
    }

    public function multiple(bool|Closure $condition = true): static
    {
        parent::multiple($condition);

        $this->multiple = (bool) $this->evaluate($condition);

        return $this;
    }

    public function minSearchLength(int $length): static
    {
        $this->minSearchLength = $length;

        return $this;
    }

    public function maxSearchResults(int $count): static
    {
        $this->maxSearchResults = $count;

        return $this;
    }

    public function searchField(string $field): static
    {
        $this->searchField = $field;

        return $this;
    }

    public function valueField(string $field): static
    {
        $this->valueField = $field;

        return $this;
    }

    public function labelField(string $field): static
    {
        $this->labelField = $field;

        return $this;
    }

    public function model(Model|Closure|array|string|null $model = null): static
    {
        $evaluatedModel = $this->evaluate($model);

        $modelClass = match (true) {
            $evaluatedModel instanceof Model => $evaluatedModel::class,
            is_string($evaluatedModel) => $evaluatedModel,
            default => null,
        };

        if ($modelClass !== null) {
            parent::model($modelClass);
        }

        $this->modelClass = $modelClass;

        $this->flushSearchCache();

        return $this;
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }

    public function getMaxSearchResults(): int
    {
        return $this->maxSearchResults;
    }

    public function getSearchField(): ?string
    {
        return $this->searchField;
    }

    public function getValueField(): ?string
    {
        return $this->valueField ?? 'id';
    }

    public function getLabelField(): ?string
    {
        return $this->labelField ?? 'name';
    }

    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    public function flushSearchCache(): static
    {
        $this->searchCache = [];
        $this->searchResults = collect();
        $this->searchQuery = null;

        return $this;
    }

    public function getSearchResults(string $search): array
    {
        $normalized = $this->normalizeSearchQuery($search);

        if ($normalized === null || ! $this->modelClass) {
            return [];
        }

        if (! array_key_exists($normalized, $this->searchCache)) {
            $results = $this->performSearch($normalized);
            $this->cacheSearchResults($normalized, $results);
        }

        $this->searchQuery = $normalized;
        $this->searchResults = $this->searchCache[$normalized];

        return $this->searchCache[$normalized]
            ->mapWithKeys(function (array $item): array {
                $value = $item['value'] ?? null;
                $label = $item['label'] ?? (is_array($item['data'] ?? null) ? ($item['data']['name'] ?? (string) $value) : (string) $value);

                return $value !== null ? [$value => $label] : [];
            })
            ->all();
    }

    public function setSearchQuery(?string $query): static
    {
        $normalized = $this->normalizeSearchQuery($query);

        $this->searchQuery = $normalized;

        if ($normalized === null || ! $this->modelClass) {
            $this->searchResults = collect();

            return $this;
        }

        if (! array_key_exists($normalized, $this->searchCache)) {
            $results = $this->performSearch($normalized);
            $this->cacheSearchResults($normalized, $results);
        }

        $this->searchResults = $this->searchCache[$normalized];

        return $this;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    protected function performSearch(string $search): Collection
    {
        $model = app($this->modelClass);
        $searchField = $this->searchField ?? $this->getLabelField();
        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        $query = $model
            ->query()
            ->where($searchField, 'like', '%'.$search.'%')
            ->limit($this->maxSearchResults);

        return $query->get()->map(function (Model $item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data' => $item->toArray(),
            ];
        });
    }

    protected function normalizeSearchQuery(?string $query): ?string
    {
        if ($query === null) {
            return null;
        }

        $trimmed = trim($query);

        if ($trimmed === '' || mb_strlen($trimmed) < $this->minSearchLength) {
            return null;
        }

        return $trimmed;
    }

    protected function cacheSearchResults(string $query, Collection $results): void
    {
        $this->searchCache[$query] = $results;
    }

    public function getViewData(): array
    {
        return [
            'searchable' => $this->getSearchable(),
            'multiple' => $this->getMultiple(),
            'minSearchLength' => $this->getMinSearchLength(),
            'maxSearchResults' => $this->getMaxSearchResults(),
            'searchField' => $this->getSearchField(),
            'valueField' => $this->getValueField(),
            'labelField' => $this->getLabelField(),
            'modelClass' => $this->getModelClass(),
            'searchResults' => $this->searchResults ?? collect(),
            'searchQuery' => $this->getSearchQuery(),
        ];
    }
}
