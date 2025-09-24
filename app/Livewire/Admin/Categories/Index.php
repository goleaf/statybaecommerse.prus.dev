<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Index
 *
 * Livewire component for Index with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $search
 * @property string $sortBy
 * @property string $sortDirection
 * @property int $perPage
 * @property mixed $queryString
 */
final class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    protected $queryString = ['search' => ['except' => ''], 'sortBy' => ['except' => 'name'], 'sortDirection' => ['except' => 'asc'], 'perPage' => ['except' => 10]];

    /**
     * Handle updatingSearch functionality with proper error handling.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle sortBy functionality with proper error handling.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $categories = Category::query()->when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%')->orWhere('description', 'like', '%'.$this->search.'%');
        })->orderBy($this->sortBy, $this->sortDirection)->paginate($this->perPage);

        return view('livewire.admin.categories.index', ['categories' => $categories]);
    }
}
