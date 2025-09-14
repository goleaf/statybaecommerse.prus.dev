<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final /**
 * LiveSearch
 * 
 * Livewire component for reactive frontend functionality.
 */
class LiveSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public bool $showResults = false;

    public int $maxResults = 10;

    public int $minQueryLength = 2;

    public bool $isSearching = false;

    public function updatedQuery(): void
    {
        if (strlen($this->query) >= $this->minQueryLength) {
            $this->isSearching = true;
            $this->performSearch();
            $this->showResults = true;
        } else {
            $this->clearResults();
        }
    }

    public function performSearch(): void
    {
        $searchService = app(SearchService::class);
        $this->results = $searchService->search($this->query, $this->maxResults);
        $this->isSearching = false;
    }

    public function clearResults(): void
    {
        $this->results = [];
        $this->showResults = false;
        $this->isSearching = false;
    }

    public function selectResult(array $result): void
    {
        $this->query = $result['title'];
        $this->clearResults();
        $this->redirect($result['url']);
    }

    public function render(): View
    {
        return view('livewire.components.live-search');
    }
}