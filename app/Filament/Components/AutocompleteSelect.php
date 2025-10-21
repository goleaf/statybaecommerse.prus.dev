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
     * @var array<string, array<int, array{value:mixed, label:string, data:array}>>
     */
    protected array $searchResultCache = [];

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

        if ($modelClass !== $this->modelClass) {
            $this->resetSearchState();
        }

        $this->modelClass = $modelClass;

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

    public function getSearchResults(string $search): array
    {
        $this->setSearchQuery($search);

        return ($this->searchResults ?? collect())
            ->mapWithKeys(function (array $item): array {
                $value = $item['value'] ?? null;
                $label = $item['label'] ?? (is_array($item['data'] ?? null) ? ($item['data']['name'] ?? (string) $value) : (string) $value);

                return $value !== null ? [$value => $label] : [];
            })
            ->all();
    }

    public function setSearchQuery(?string $query): static
    {
        $normalizedQuery = $this->normalizeSearchQuery($query);

        $this->searchQuery = $normalizedQuery;
        $this->searchResults = $normalizedQuery !== null
            ? $this->performSearch($normalizedQuery)
            : collect();

        return $this;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    protected function performSearch(string $search): Collection
    {
        if ($this->shouldSkipSearch($search)) {
            return collect();
        }

        $cacheKey = $this->cacheKey($search);

        if (array_key_exists($cacheKey, $this->searchResultCache)) {
            return collect($this->searchResultCache[$cacheKey]);
        }

        $model = app($this->modelClass);
        $searchField = $this->searchField ?? $this->getLabelField();
        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        $results = $model
            ->newQuery()
            ->where($searchField, 'like', '%'.$search.'%')
            ->limit($this->maxSearchResults)
            ->get()
            ->map(function (Model $item) use ($valueField, $labelField): array {
                return [
                    'value' => $item->{$valueField},
                    'label' => $item->{$labelField},
                    'data' => $item->toArray(),
                ];
            })
            ->values()
            ->all();

        $this->searchResultCache[$cacheKey] = $results;

        return collect($results);
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

    protected function shouldSkipSearch(string $search): bool
    {
        return $this->modelClass === null
            || mb_strlen($search) < $this->minSearchLength;
    }

    protected function normalizeSearchQuery(?string $query): ?string
    {
        if ($query === null) {
            return null;
        }

        $trimmed = trim($query);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function cacheKey(string $search): string
    {
        return mb_strtolower($search);
    }

    protected function resetSearchState(): void
    {
        $this->searchResults = collect();
        $this->searchQuery = null;
        $this->searchResultCache = [];
    }
}
