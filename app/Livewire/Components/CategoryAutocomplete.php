<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\AutocompleteService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CategoryAutocomplete extends Component
{
    #[Validate('nullable|string|max:255')]
    public string $query = '';

    public array $results = [];

    public bool $showResults = false;

    public int $maxResults = 10;

    public int $minQueryLength = 2;

    public bool $isSearching = false;

    public ?int $selectedCategoryId = null;

    public string $selectedCategoryName = '';

    public bool $required = false;

    public string $placeholder = '';

    public string $name = 'category_id';

    public bool $allowMultiple = false;

    public array $selectedCategories = [];

    public function mount(
        ?int $selectedCategoryId = null,
        string $selectedCategoryName = '',
        bool $required = false,
        string $placeholder = '',
        string $name = 'category_id',
        bool $allowMultiple = false,
        array $selectedCategories = []
    ): void {
        $this->selectedCategoryId = $selectedCategoryId;
        $this->selectedCategoryName = $selectedCategoryName;
        $this->required = $required;
        $this->placeholder = $placeholder ?: __('admin.category.select_category');
        $this->name = $name;
        $this->allowMultiple = $allowMultiple;
        $this->selectedCategories = $selectedCategories;
        
        if ($selectedCategoryId && $selectedCategoryName) {
            $this->query = $selectedCategoryName;
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
        $results = $autocompleteService->searchCategories($this->query, $this->maxResults);
        
        // Format results for autocomplete
        $this->results = array_map(function ($result) {
            return [
                'id' => $result['id'],
                'title' => $result['title'],
                'subtitle' => $result['subtitle'],
                'description' => $result['description'] ?? '',
                'image' => $result['image'],
                'products_count' => $result['products_count'] ?? 0,
                'children_count' => $result['children_count'] ?? 0,
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
        if ($this->allowMultiple) {
            // Add to multiple selection
            $this->selectedCategories[] = [
                'id' => $result['id'],
                'name' => $result['title'],
                'subtitle' => $result['subtitle'],
            ];
            
            $this->dispatch('categories-updated', $this->selectedCategories);
        } else {
            // Single selection
            $this->selectedCategoryId = $result['id'];
            $this->selectedCategoryName = $result['title'];
            $this->query = $result['title'];
            
            $this->dispatch('category-selected', [
                'id' => $result['id'],
                'name' => $result['title'],
                'subtitle' => $result['subtitle'],
            ]);
        }
        
        $this->clearResults();
    }

    public function removeCategory(int $categoryId): void
    {
        $this->selectedCategories = array_filter(
            $this->selectedCategories,
            fn($category) => $category['id'] !== $categoryId
        );
        
        $this->dispatch('categories-updated', $this->selectedCategories);
    }

    public function clearSelection(): void
    {
        if ($this->allowMultiple) {
            $this->selectedCategories = [];
            $this->dispatch('categories-updated', $this->selectedCategories);
        } else {
            $this->selectedCategoryId = null;
            $this->selectedCategoryName = '';
            $this->dispatch('category-cleared');
        }
        
        $this->query = '';
        $this->clearResults();
    }

    public function render(): View
    {
        return view('livewire.components.category-autocomplete');
    }
}
