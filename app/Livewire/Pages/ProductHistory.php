<?php declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

final class ProductHistory extends Component
{
    use WithPagination;

    public Product $product;
    public int $perPage = 20;

    protected $listeners = ['refreshHistory' => '$refresh'];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function getHistoryProperty()
    {
        return Activity::query()
            ->where('subject_type', Product::class)
            ->where('subject_id', $this->product->id)
            ->orWhere(function ($query) {
                $query->where('subject_type', Product::class)
                    ->whereJsonContains('properties->attributes', ['product_id' => $this->product->id]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.pages.product-history', [
            'history' => $this->history,
            'product' => $this->product,
        ])->layout('layouts.app', [
            'title' => __('frontend.products.history_title', ['product' => $this->product->trans('name') ?? $this->product->name]),
        ]);
    }
}

