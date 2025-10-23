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

    /**
     * @var class-string<Model>|null
     */
    protected ?string $modelClass = null;

    /**
     * @var Collection<int, array{value: string, label: string, data: array<string, mixed>}>|null
     */
    protected ?Collection $searchResults = null;

    protected ?string $searchQuery = null;

    /**
     * @var array<string, array<int, array{value: string, label: string, data: array<string, mixed>}>>
     */
    protected array $searchResultCache = [];

    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function searchable(Closure|array|bool $condition = true): static
    {
        parent::searchable($condition);

        $evaluated = $this->evaluate($condition);

        $this->searchable = is_array($evaluated) ? true : (bool) $evaluated;

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
            is_string($evaluatedModel)       => $evaluatedModel,
            default                          => null,
        };

        if ($modelClass !== null) {
            parent::model($modelClass);
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

    public function getValueField(): string
    {
        return $this->valueField ?? 'id';
    }

    public function getLabelField(): string
    {
        return $this->labelField ?? 'name';
    }

    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    public function getSearchResults(?string $search = null): array
    {
        if ($search !== null) {
            $this->setSearchQuery($search);
        } elseif ($this->searchResults === null) {
            $this->performSearch();
        }

        return ($this->searchResults ?? collect())
            ->mapWithKeys(
                /**
                 * @param  array{value: string, label: string, data: array<string, mixed>} $item
                 * @return array<string, string>
                 */
                function (array $item, int|string $_): array {
                    $value = $item['value'];
                    $label = $item['label'];
                    $data = $item['data'] ?? [];

                    // Support both the normalised payload and legacy flat metadata.
                    $payload = [];

                    if (is_array($data)) {
                        $payload = is_array($data['payload'] ?? null) ? $data['payload'] : $data;
                    }

                    if ($label === '' && array_key_exists('name', $payload)) {
                        $name = $payload['name'];

                        if (is_string($name)) {
                            $label = $name;
                        }
                    }

                    if ($label === '') {
                        $label = $value;
                    }

                    return [$value => $label];
                },
            )
            ->all();
    }

    public function setSearchQuery(?string $query): static
    {
        $normalizedQuery = $this->normalizeSearchQuery($query);

        $this->searchQuery = $normalizedQuery;

        if ($normalizedQuery === null) {
            $this->searchResults = $this->emptyResults();

            return $this;
        }

        $this->searchResults = $this->performSearch($normalizedQuery);

        return $this;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    /**
     * @return Collection<int, array{value: string, label: string, data: array<string, mixed>}>
     */
    protected function performSearch(string $search): Collection
    {
        $normalizedSearch = $this->normalizeSearchQuery($search);

        if ($normalizedSearch === null || $this->shouldSkipSearch($normalizedSearch) || $this->modelClass === null) {
            return $this->emptyResults();
        }

        $cacheKey = $this->cacheKey($normalizedSearch);

        if (array_key_exists($cacheKey, $this->searchResultCache)) {
            /** @var array<int, array{value: string, label: string, data: array<string, mixed>}> $cachedResults */
            $cachedResults = $this->searchResultCache[$cacheKey];

            /** @var Collection<int, array{value: string, label: string, data: array<string, mixed>}> $collection */
            $collection = new Collection($cachedResults);

            return $collection;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->modelClass;

        $model = app($modelClass);

        if (! $model instanceof Model) {
            return $this->emptyResults();
        }

        $searchField = $this->searchField ?? $this->getLabelField();

        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        $query = $model
            ->query()
            ->where($searchField, 'like', '%' . $this->searchQuery . '%')
            ->limit($this->maxSearchResults);

        $this->searchResults = $query->get()->map(function (Model $item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data'  => $item->toArray(),
            ];
        });
    }

    public function getViewData(): array
    {
        return [
            'searchable'       => $this->getSearchable(),
            'multiple'         => $this->getMultiple(),
            'minSearchLength'  => $this->getMinSearchLength(),
            'maxSearchResults' => $this->getMaxSearchResults(),
            'searchField'      => $this->getSearchField(),
            'valueField'       => $this->getValueField(),
            'labelField'       => $this->getLabelField(),
            'modelClass'       => $this->getModelClass(),
            'searchResults'    => $this->searchResults ?? collect(),
            'searchQuery'      => $this->getSearchQuery(),
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
        return mb_strtolower(trim($search));
    }

    protected function resetSearchState(): void
    {
        $this->searchResults = $this->emptyResults();
        $this->searchQuery = null;
        $this->searchResultCache = [];
    }

    /**
     * @return Collection<int, array{value: string, label: string, data: array<string, mixed>}>
     */
    protected function emptyResults(): Collection
    {
        /** @var Collection<int, array{value: string, label: string, data: array<string, mixed>}> $collection */
        $collection = new Collection;

        return $collection;
    }
}
