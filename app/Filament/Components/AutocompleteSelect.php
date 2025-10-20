<?php

declare(strict_types=1);

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
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

    protected ?string $modelClass = null;

    protected ?Collection $searchResults = null;

    protected ?string $searchQuery = null;

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

    /**
     * @return array<string, string>
     */
    public function getSearchResults(string $search = ''): array
    {
        $results = func_num_args() === 0
            ? $this->resolveSearchResults()
            : $this->resolveSearchResults($search);

        return $this->mapSearchResultsForOptions($results);
    }

    public function getSearchResultsCollection(?string $search = null): Collection
    {
        return $this->resolveSearchResults($search);
    }

    /**
     * @return array<string, string>
     */
    protected function mapSearchResultsForOptions(Collection $results): array
    {
        return $results
            ->mapWithKeys(function (array $item): array {
                $value = $item['value'] ?? null;
                $label = $item['label']
                    ?? (is_array($item['data'] ?? null)
                        ? ($item['data'][$this->getLabelField() ?? 'name'] ?? (string) $value)
                        : (string) $value);

                return $value !== null ? [$value => $label] : [];
            })
            ->all();
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
        $this->searchQuery = $query;
        $this->performSearch();

        return $this;
    }

    public function getSearchQuery(): ?string
    {
        return $this->searchQuery;
    }

    protected function performSearch(): void
    {
        if (! $this->modelClass || ! $this->searchQuery || strlen($this->searchQuery) < $this->minSearchLength) {
            $this->searchResults = collect();

            return;
        }

        $model = app($this->modelClass);
        $searchField = $this->searchField ?? $this->getLabelField();
        $valueField = $this->getValueField();
        $labelField = $this->getLabelField();

        $query = $model
            ->query()
            ->where($searchField, 'like', '%'.$this->searchQuery.'%')
            ->limit($this->maxSearchResults);

        $this->searchResults = $query->get()->map(function (Model $item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data' => $item->toArray(),
            ];
        });
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
