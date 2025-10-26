<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Throwable;

/**
 * AutocompleteSelect Component
 *
 * A custom Filament form component that provides autocomplete functionality
 * for select fields with search capabilities.
 */
final class AutocompleteSelect extends Select
{
    protected string $view = 'filament.components.autocomplete-select';

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

    protected ?string $activeModelForCache = null;

    public static function make(?string $name = null): static
    {
        $component = parent::make($name);

        $component->searchable(true);
        $component->searchResults = $component->emptyResults();

        if (is_string($name) && $name !== '') {
            $segments = preg_split('/[_\s\-]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if ($segments !== []) {
                $defaultQuery = $component->normalizeSearchQuery($segments[0]);

                if ($defaultQuery !== null) {
                    $component->searchQuery = $defaultQuery;
                }
            }
        }

        return $component;
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

    /**
     * @param Model|array<string, mixed>|class-string<Model>|Closure|null $model
     */
    // @phpstan-ignore-next-line We normalise the evaluated model input before deferring to the parent implementation.
    public function model(Model|Closure|array|string|null $model = null): static
    {
        $evaluatedModel = $this->evaluate($model);

        $modelClass = match (true) {
            $evaluatedModel instanceof Model                                        => $evaluatedModel::class,
            is_string($evaluatedModel) && is_a($evaluatedModel, Model::class, true) => $evaluatedModel,
            default                                                                 => null,
        };

        if ($modelClass !== null) {
            /** @var class-string<Model> $modelClass */
            parent::model($modelClass);
        }

        if ($modelClass !== $this->modelClass) {
            $previousQuery = $this->searchQuery;
            $this->modelClass = $modelClass;
            $this->resetSearchState();
            $this->searchQuery = $previousQuery;

            return $this;
        }

        $this->modelClass = $modelClass;

        return $this;
    }

    public function getSearchable(): bool
    {
        return $this->isSearchable();
    }

    public function getMultiple(): bool
    {
        return $this->isMultiple();
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

    /**
     * @return array<string, string>
     */
    public function getSearchResults(string $search): array
    {
        $normalizedQuery = $this->normalizeSearchQuery($search);

        if ($normalizedQuery === null) {
            $this->setSearchQuery(null);

            return [];
        }

        if ($this->getModelClass() === null) {
            return [];
        }

        $results = $this->performSearch($normalizedQuery);

        $this->searchResults = $results;
        $this->searchQuery = $normalizedQuery;

        return $this->formatResultsForSelect($results);
    }

    public function setSearchQuery(?string $query): static
    {
        $normalizedQuery = $this->normalizeSearchQuery($query);

        $this->searchQuery = $normalizedQuery;

        if ($normalizedQuery === null) {
            $this->searchResults = $this->emptyResults();
            $this->flushSearchCache();

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

        $modelClass = $this->getModelClass();

        if ($normalizedSearch === null || $this->shouldSkipSearch($normalizedSearch) || $modelClass === null) {
            return $this->emptyResults();
        }

        if ($this->activeModelForCache !== $modelClass) {
            // New model context detected, so drop any cached payloads from previous lookups.
            $this->flushSearchCache();
            $this->activeModelForCache = $modelClass;
        }

        $cacheKey = $this->cacheKey($normalizedSearch, $modelClass);

        if (array_key_exists($cacheKey, $this->searchResultCache)) {
            /** @var array<int, array{value: string, label: string, data: array<string, mixed>}> $cachedResults */
            $cachedResults = $this->searchResultCache[$cacheKey];

            /** @var Collection<int, array{value: string, label: string, data: array<string, mixed>}> $collection */
            $collection = new Collection($cachedResults);

            return $collection;
        }

        $model = app($modelClass);

        if (! $model instanceof Model) {
            return $this->emptyResults();
        }

        $searchField = $this->searchField ?? $this->getLabelField();

        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        // Avoid missing records when the model registers global scopes (common for admin lookups).
        $query = $model->newQuery()->withoutGlobalScopes();

        $terms = preg_split('/\s+/u', $normalizedSearch) ?: [$normalizedSearch];

        $query->where(function (Builder $builder) use ($terms, $searchField): void {
            foreach ($terms as $term) {
                $builder->where($searchField, 'like', '%' . $term . '%');
            }
        });

        try {
            $resultsArray = $query
                ->limit($this->maxSearchResults)
                ->get()
                ->flatMap(function (Model $item) use ($valueField, $labelField): array {
                    $rawValue = $item->getAttribute($valueField);

                    if ($rawValue === null) {
                        return [];
                    }

                    if (! is_scalar($rawValue) && ! (is_object($rawValue) && method_exists($rawValue, '__toString'))) {
                        return [];
                    }

                    $value = (string) $rawValue;

                    $rawLabel = $item->getAttribute($labelField);
                    $label = is_string($rawLabel) ? $rawLabel : $value;

                    /** @var array<string, mixed> $data */
                    $data = $item->toArray();

                    return [[
                        'value' => $value,
                        'label' => $label,
                        'data'  => $data,
                    ]];
                })
                ->values()
                ->all();
        } catch (Throwable) {
            $this->searchResultCache[$cacheKey] = [];

            return $this->emptyResults();
        }

        /** @var array<int, array{value: string, label: string, data: array<string, mixed>}> $resultsArray */
        $this->searchResultCache[$cacheKey] = $resultsArray;

        /** @var Collection<int, array{value: string, label: string, data: array<string, mixed>}> $results */
        $results = new Collection($resultsArray);

        return $results;
    }

    public function getViewData(): array
    {
        $results = $this->searchResults ?? $this->emptyResults();
        $modelClass = $this->getModelClass();
        $shouldExposeResults = $this->activeModelForCache === $modelClass && $modelClass !== null;

        if ($shouldExposeResults && $this->searchQuery !== null && $results->isEmpty()) {
            $results = $this->performSearch($this->searchQuery);
            $this->searchResults = $results;
        }

        return [
            'searchable'       => $this->getSearchable(),
            'multiple'         => $this->getMultiple(),
            'minSearchLength'  => $this->getMinSearchLength(),
            'maxSearchResults' => $this->getMaxSearchResults(),
            'searchField'      => $this->getSearchField(),
            'valueField'       => $this->getValueField(),
            'labelField'       => $this->getLabelField(),
            'modelClass'       => $this->getModelClass(),
            'searchResults'    => $shouldExposeResults ? $this->formatResultsForSelect($results) : [],
            'searchResultItems' => $shouldExposeResults ? $results->values()->all() : [],
            'searchQuery' => $this->getSearchQuery(),
        ];
    }

    protected function shouldSkipSearch(string $search): bool
    {
        return $this->getModelClass() === null
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

    protected function cacheKey(string $search, ?string $modelClass = null): string
    {
        $prefix = $modelClass ?? 'global';

        return $prefix . '::' . mb_strtolower(trim($search));
    }

    protected function resetSearchState(): void
    {
        $this->searchResults = $this->emptyResults();
        $this->searchQuery = null;
        $this->flushSearchCache();
    }

    protected function flushSearchCache(): void
    {
        $this->searchResultCache = [];
        $this->activeModelForCache = $this->getModelClass();
    }

    /**
     * @param Collection<int, array{value: string, label: string, data: array<string, mixed>}> $results
     * @return array<string, string>
     */
    protected function formatResultsForSelect(Collection $results): array
    {
        return $results
            ->mapWithKeys(
                /**
                 * @param array{value: string, label: string, data: array<string, mixed>} $item
                 */
                static function (array $item): array {
                    $value = $item['value'];
                    $label = $item['label'];
                    $data = $item['data'];

                    // Prefer a nested payload while maintaining compatibility with any legacy flat arrays.
                    $payload = $data['payload'] ?? $data;

                    if (! is_array($payload)) {
                        $payload = $data;
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

    /**
     * @return Collection<int, array{value: string, label: string, data: array<string, mixed>}>
     */
    protected function emptyResults(): Collection
    {
        return collect();
    }
}
