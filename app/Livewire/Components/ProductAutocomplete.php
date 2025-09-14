<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class ProductAutocomplete extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public bool $showResults = false;

    public int $maxResults = 10;

    public int $minQueryLength = 2;

    public bool $isSearching = false;

    public ?int $selectedProductId = null;

    public string $selectedProductName = '';

    public bool $required = false;

    public string $placeholder = '';

    public string $name = 'product_id';

    public function mount(
        ?int $selectedProductId = null,
        string $selectedProductName = '',
        bool $required = false,
        string $placeholder = '',
        string $name = 'product_id'
    ): void {
        $this->selectedProductId = $selectedProductId;
        $this->selectedProductName = $selectedProductName;
        $this->required = $required;
        $this->placeholder = $placeholder ?: __('admin.product.select_product');
        $this->name = $name;
        
        if ($selectedProductId && $selectedProductName) {
            $this->query = $selectedProductName;
        }
    }

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
        $autocompleteService = app(AutocompleteService::class);
        $results = $autocompleteService->searchProducts($this->query, $this->maxResults);
        
        // Format results for autocomplete
        $this->results = array_map(function ($result) {
            return [
                'id' => $result['id'],
                'title' => $result['title'],
                'subtitle' => $result['subtitle'],
                'sku' => $result['sku'] ?? '',
                'price' => $result['formatted_price'] ?? '',
                'image' => $result['image'],
                'in_stock' => $result['in_stock'] ?? false,
            ];
        }, $results);
        
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
        $this->selectedProductId = $result['id'];
        $this->selectedProductName = $result['title'];
        $this->query = $result['title'];
        $this->clearResults();
        
        // Emit event for parent component
        $this->dispatch('product-selected', [
            'id' => $result['id'],
            'name' => $result['title'],
            'sku' => $result['sku'],
            'price' => $result['price'],
        ]);
    }

    public function clearSelection(): void
    {
        $this->selectedProductId = null;
        $this->selectedProductName = '';
        $this->query = '';
        $this->clearResults();
        
        // Emit event for parent component
        $this->dispatch('product-cleared');
    }

    public function render(): View
    {
        return view('livewire.components.product-autocomplete');
    }
}
