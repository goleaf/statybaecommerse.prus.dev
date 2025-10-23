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
    protected array $searchCache = [];

    protected ?string $activeModelForCache = null;

    public static function make(?string $name = null): static
    {
        $component = parent::make($name);

        $component->searchable(true);

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
            // Changing the model invalidates cached results and view payloads.
            $this->resetSearchState();
        }

        $this->modelClass = $modelClass;

        $this->flushSearchCache();

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
        $normalized = $this->normalizeSearchQuery($search);

        return ($this->searchResults ?? collect())
            ->mapWithKeys(
                /**
                 * @param  array{value: string, label: string, data: array<string, mixed>} $item
                 * @return array<string, string>
                 */
                function (array $item, int|string $_): array {
                    $value = $item['value'];
                    $label = $item['label'];
                    $data = $item['data'];

                    // Support both the normalised payload and legacy flat metadata by preferring a nested payload when present.
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

    public function setSearchQuery(?string $query): static
    {
        $normalized = $this->normalizeSearchQuery($query);

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

        $modelClass = $this->getModelClass();

        if ($normalizedSearch === null || $this->shouldSkipSearch($normalizedSearch) || $modelClass === null) {
            return $this->emptyResults();
        }

        if ($this->activeModelForCache !== $modelClass) {
            // New model context detected, so drop any cached payloads from previous lookups.
            $this->searchResultCache = [];
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

        $query = $model->newQuery();

        // Avoid missing records when the model registers global scopes (common for admin lookups).
        $query = $query->withoutGlobalScopes();

        $resultsArray = $query
            ->where($searchField, 'like', '%' . $normalizedSearch . '%')
            ->limit($this->maxSearchResults)
            ->get()
            ->flatMap(function (Model $item) use ($valueField, $labelField): array {
                $rawValue = $item->getAttribute($valueField);

                if ($rawValue === null) {
                    return [];
                }

                if (! is_scalar($rawValue)) {
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

        /** @var array<int, array{value: string, label: string, data: array<string, mixed>}> $resultsArray */
        $this->searchResultCache[$cacheKey] = $resultsArray;

        /** @var Collection<int, array{value: string, label: string, data: array<string, mixed>}> $results */
        $results = new Collection($resultsArray);

        return $results;
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
            'searchResults'    => $this->activeModelForCache === $this->getModelClass()
                ? $this->searchResults ?? $this->emptyResults()
                : $this->emptyResults(),
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
        $this->searchResultCache = [];
        $this->activeModelForCache = null;
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
