<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class VoiceSearch extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public bool $isListening = false;

    public bool $isSupported = false;

    public string $status = '';

    public int $maxResults = 10;

    public int $minQueryLength = 2;

    public array $searchTypes = ['products', 'categories', 'brands'];

    public function mount(): void
    {
        $this->isSupported = $this->checkVoiceSupport();
    }

    public function startListening(): void
    {
        if (!$this->isSupported) {
            $this->status = 'Voice search not supported in this browser';
            return;
        }

        $this->isListening = true;
        $this->status = 'Listening...';
        
        $this->dispatch('start-voice-recognition');
    }

    public function stopListening(): void
    {
        $this->isListening = false;
        $this->status = '';
        
        $this->dispatch('stop-voice-recognition');
    }

    public function processVoiceResult(string $transcript): void
    {
        $this->query = trim($transcript);
        $this->isListening = false;
        $this->status = '';
        
        if (strlen($this->query) >= $this->minQueryLength) {
            $this->performSearch();
        }
    }

    public function performSearch(): void
    {
        if (strlen($this->query) < $this->minQueryLength) {
            return;
        }

        $autocompleteService = app(AutocompleteService::class);
        $results = $autocompleteService->search($this->query, $this->maxResults, $this->searchTypes);
        
        $this->dispatch('search-completed', [
            'query' => $this->query,
            'results' => $results
        ]);
    }

    public function clearQuery(): void
    {
        $this->query = '';
        $this->status = '';
    }

    private function checkVoiceSupport(): bool
    {
        // This will be checked on the frontend
        return true;
    }

    public function render(): View
    {
        return view('livewire.components.voice-search');
    }
}
