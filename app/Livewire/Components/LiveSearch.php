<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * LiveSearch
 *
 * Livewire component for LiveSearch with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $query
 * @property array $results
 * @property array $suggestions
 * @property bool $showResults
 * @property bool $showSuggestions
 * @property int $maxResults
 * @property int $minQueryLength
 * @property bool $isSearching
 * @property array $searchTypes
 * @property bool $enableSuggestions
 * @property bool $enableRecentSearches
 * @property bool $enablePopularSearches
 */
final class LiveSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public array $suggestions = [];

    public bool $showResults = false;

    public bool $showSuggestions = false;

    public int $maxResults = 10;

    public int $minQueryLength = 2;

    public bool $isSearching = false;

    public array $searchTypes = ['products', 'categories', 'brands', 'collections'];

    public bool $enableSuggestions = true;

    public bool $enableRecentSearches = true;

    public bool $enablePopularSearches = true;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
        }
    }

    /**
     * Handle updatedQuery functionality with proper error handling.
     */
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

    /**
     * Handle performSearch functionality with proper error handling.
     */
    public function performSearch(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $this->results = $autocompleteService->search($this->query, $this->maxResults, $this->searchTypes);
        $this->isSearching = false;
    }

    /**
     * Handle loadSuggestions functionality with proper error handling.
     */
    public function loadSuggestions(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $suggestions = [];
        if ($this->enableRecentSearches) {
            $recent = $autocompleteService->getRecentSuggestions(3);
            $suggestions = array_merge($suggestions, $recent);
        }
        if ($this->enablePopularSearches) {
            $popular = $autocompleteService->getPopularSuggestions(7);
            $suggestions = array_merge($suggestions, $popular);
        }
        $this->suggestions = array_slice($suggestions, 0, 10);
    }

    /**
     * Handle clearResults functionality with proper error handling.
     */
    public function clearResults(): void
    {
        $this->results = [];
        $this->showResults = false;
        $this->isSearching = false;
    }

    /**
     * Handle clearQuery functionality with proper error handling.
     */
    public function clearQuery(): void
    {
        $this->query = '';
        $this->clearResults();
        if ($this->enableSuggestions) {
            $this->loadSuggestions();
            $this->showSuggestions = true;
        }
    }

    /**
     * Handle selectResult functionality with proper error handling.
     */
    public function selectResult(array $result): void
    {
        $this->query = $result['title'];
        $this->clearResults();
        $this->redirect($result['url']);
    }

    /**
     * Handle selectSuggestion functionality with proper error handling.
     */
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

    /**
     * Handle clearRecentSearches functionality with proper error handling.
     */
    public function clearRecentSearches(): void
    {
        $autocompleteService = app(AutocompleteService::class);
        $autocompleteService->clearRecentSearches();
        $this->loadSuggestions();
    }

    /**
     * Handle toggleSearchType functionality with proper error handling.
     */
    public function toggleSearchType(string $type): void
    {
        if (in_array($type, $this->searchTypes)) {
            $this->searchTypes = array_filter($this->searchTypes, fn ($t) => $t !== $type);
        } else {
            $this->searchTypes[] = $type;
        }
        if (! empty($this->query) && strlen($this->query) >= $this->minQueryLength) {
            $this->performSearch();
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.live-search');
    }
}
