<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class MobileAutocomplete extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public array $suggestions = [];

    public bool $showResults = false;

    public bool $showSuggestions = false;

    public int $maxResults = 5;

    public int $minQueryLength = 1;

    public bool $isSearching = false;

    public array $searchTypes = ['products'];

    public bool $enableSuggestions = true;

    public bool $enableRecentSearches = true;

    public bool $enablePopularSearches = true;

    public bool $isFullScreen = false;

    public function mount(): void
    {
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
        }
    }

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= $this->minQueryLength) {
            $this->isSearching = true;
            $this->performSearch();
            $this->showResults = true;
            $this->showSuggestions = false;
        } else {
            $this->clearResults();
            if ($this->enableSuggestions && empty($this->query)) {
                $this->loadSuggestions();
                $this->showSuggestions = true;
            } else {
                $this->showSuggestions = false;
            }
        }
    }

    public function performSearch(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $this->results = $autocompleteService->search($this->query, $this->maxResults, $this->searchTypes);
        $this->isSearching = false;
    }

    public function loadSuggestions(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $suggestions = [];

        if ($this->enableRecentSearches) {
            $recent = $autocompleteService->getRecentSuggestions(2);
            $suggestions = array_merge($suggestions, $recent);
        }

        if ($this->enablePopularSearches) {
            $popular = $autocompleteService->getPopularSuggestions(3);
            $suggestions = array_merge($suggestions, $popular);
        }

        $this->suggestions = array_slice($suggestions, 0, 5);
    }

    public function clearQuery(): void
    {
        $this->query = '';
        $this->clearResults();
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
            $this->showSuggestions = true;
        }
    }

    public function selectSuggestion(array $suggestion): void
    {
        if (isset($suggestion['search_term'])) {
            $this->query = $suggestion['search_term'];
        } else {
            $this->query = $suggestion['title'];
        }
        $this->showSuggestions = false;
        $this->updatedQuery();
    }

    public function selectResult(array $result): void
    {
        $this->dispatch('result-selected', $result);
        $this->clearQuery();
    }

    public function clearResults(): void
    {
        $this->results = [];
        $this->showResults = false;
        $this->isSearching = false;
    }

    public function toggleFullScreen(): void
    {
        $this->isFullScreen = !$this->isFullScreen;
    }

    public function render(): View
    {
        return view('livewire.components.mobile-autocomplete');
    }
}
