<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Renderless;

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

    protected ?array $formattedResultsCache = null;

    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function searchable(Closure|array|bool $condition = true): static
    {
        parent::searchable($condition);

        $evaluatedCondition = $this->evaluate($condition);

        $this->searchable = match (true) {
            is_array($evaluatedCondition) => true,
            default => (bool) $evaluatedCondition,
        };

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

    public function getSearchResults(string $search = ''): array
    {
        $search = trim($search);

        if ($this->searchQuery === $search && $this->formattedResultsCache !== null) {
            return $this->formattedResultsCache;
        }

        $this->searchQuery = $search;

        $this->searchResults = $this->performSearch($search);

        return $this->formattedResultsCache = $this->formatResults($this->searchResults);
    }

    protected function resolveSearchResults(?string $search = null): Collection
    {
        if ($search !== null) {
            $this->setSearchQuery($search);
        } elseif ($this->searchResults === null) {
            $this->performSearch();
        }

        return $this->searchResults ?? collect();
    }

    #[ExposedLivewireMethod]
    #[Renderless]
    public function getSearchResultsForJs(string $search): array
    {
        return $this->transformOptionsForJs($this->getSearchResults($search));
    }

    public function setSearchQuery(?string $query): static
    {
        $query = $query !== null ? trim($query) : null;

        $this->searchQuery = $query;

        if (empty($query)) {
            $this->searchResults = collect();
            $this->formattedResultsCache = [];

            return $this;
        }

        $this->formattedResultsCache = $this->getSearchResults($query);

        return $this;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    protected function performSearch(string $search): Collection
    {
        if (! $this->modelClass || $search === '' || mb_strlen($search) < $this->minSearchLength) {
            return collect();
        }

        /** @var Model $model */
        $model = app($this->modelClass);
        $searchField = $this->searchField ?? $this->getLabelField();

        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        $terms = collect(preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY));

        $query = $model
            ->newQuery()
            ->when(
                $terms->isNotEmpty(),
                fn (Builder $builder): Builder => $builder->where(function (Builder $query) use ($searchField, $terms): void {
                    foreach ($terms as $term) {
                        $query->where($searchField, 'like', '%'.$term.'%');
                    }
                }),
            )
            ->limit($this->maxSearchResults);

        return $query->get()->map(function (Model $item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data'  => $item->toArray(),
            ];
        });
    }

    /**
     * @param  Collection<int, array{value:mixed,label:scalar|null,data:array}>  $results
     * @return array<mixed, scalar|null>
     */
    protected function formatResults(Collection $results): array
    {
        return $results
            ->mapWithKeys(function (array $item): array {
                $value = $item['value'] ?? null;
                $label = $item['label']
                    ?? (is_array($item['data'] ?? null)
                        ? ($item['data']['name'] ?? (string) $value)
                        : (string) $value);

                return $value !== null ? [$value => $label] : [];
            })
            ->all();
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
