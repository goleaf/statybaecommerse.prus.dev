<?php

declare (strict_types=1);
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;
/**
 * ProductHistoryController
 * 
 * HTTP controller handling ProductHistoryController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class ProductHistoryController extends Controller
{
    /**
     * Display the specified resource with related data.
     * @param Product $product
     * @return View
     */
    public function show(Product $product): View
    {
        return view('livewire.pages.product-history', ['product' => $product]);
    }
}