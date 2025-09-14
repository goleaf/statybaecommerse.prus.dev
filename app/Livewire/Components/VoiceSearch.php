<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
/**
 * VoiceSearch
 * 
 * Livewire component for VoiceSearch with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property string $query
 * @property bool $isListening
 * @property bool $isSupported
 * @property string $status
 * @property int $maxResults
 * @property int $minQueryLength
 * @property array $searchTypes
 */
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
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->isSupported = $this->checkVoiceSupport();
    }
    /**
     * Handle startListening functionality with proper error handling.
     * @return void
     */
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
    /**
     * Handle stopListening functionality with proper error handling.
     * @return void
     */
    public function stopListening(): void
    {
        $this->isListening = false;
        $this->status = '';
        $this->dispatch('stop-voice-recognition');
    }
    /**
     * Handle processVoiceResult functionality with proper error handling.
     * @param string $transcript
     * @return void
     */
    public function processVoiceResult(string $transcript): void
    {
        $this->query = trim($transcript);
        $this->isListening = false;
        $this->status = '';
        if (strlen($this->query) >= $this->minQueryLength) {
            $this->performSearch();
        }
    }
    /**
     * Handle performSearch functionality with proper error handling.
     * @return void
     */
    public function performSearch(): void
    {
        if (strlen($this->query) < $this->minQueryLength) {
            return;
        }
        $autocompleteService = app(AutocompleteService::class);
        $results = $autocompleteService->search($this->query, $this->maxResults, $this->searchTypes);
        $this->dispatch('search-completed', ['query' => $this->query, 'results' => $results]);
    }
    /**
     * Handle clearQuery functionality with proper error handling.
     * @return void
     */
    public function clearQuery(): void
    {
        $this->query = '';
        $this->status = '';
    }
    /**
     * Handle checkVoiceSupport functionality with proper error handling.
     * @return bool
     */
    private function checkVoiceSupport(): bool
    {
        // This will be checked on the frontend
        return true;
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.voice-search');
    }
}