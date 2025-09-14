<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProductHistoryController extends Controller
{
    public function show(Product $product): View
    {
        return view('livewire.pages.product-history', [
            'product' => $product
        ]);
    }
}
