<?php

declare(strict_types=1);

namespace App\Filament\Components;

use App\Services\AutocompleteService;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class AutocompleteSelect extends Select
{
    protected string $autocompleteType = 'products';
    protected int $maxResults = 10;
    protected bool $enableFuzzy = false;
    protected bool $enablePersonalized = false;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function autocompleteType(string $type): static
    {
        $this->autocompleteType = $type;
        return $this;
    }

    public function maxResults(int $maxResults): static
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    public function enableFuzzy(bool $enable = true): static
    {
        $this->enableFuzzy = $enable;
        return $this;
    }

    public function enablePersonalized(bool $enable = true): static
    {
        $this->enablePersonalized = $enable;
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchable()
            ->preload()
            ->getSearchResultsUsing(function (string $search): array {
                if (strlen($search) < 2) {
                    return [];
                }

                $autocompleteService = app(AutocompleteService::class);
                
                if ($this->enableFuzzy) {
                    $results = $autocompleteService->searchWithFuzzy($search, $this->maxResults, [$this->autocompleteType]);
                } else {
                    $results = $autocompleteService->search($search, $this->maxResults, [$this->autocompleteType]);
                }

                $options = [];
                foreach ($results as $result) {
                    $options[$result['id']] = $result['title'];
                }

                return $options;
            })
            ->getOptionLabelUsing(function ($value): string {
                if (!$value) {
                    return '';
                }

                $autocompleteService = app(AutocompleteService::class);
                $results = $autocompleteService->searchById($value, $this->autocompleteType);
                
                return $results['title'] ?? (string) $value;
            });
    }

    public function getAutocompleteType(): string
    {
        return $this->autocompleteType;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function isFuzzyEnabled(): bool
    {
        return $this->enableFuzzy;
    }

    public function isPersonalizedEnabled(): bool
    {
        return $this->enablePersonalized;
    }
}
