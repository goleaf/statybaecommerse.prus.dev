<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Frontend\DataProviders\CategoryCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    public function __construct(private readonly CategoryCatalogueDataProvider $dataProvider)
    {
    }

    public function index(Request $request): View
    {
        $data = $this->dataProvider->index();

        return view('frontend.categories.index', $data);
    }

    public function show(Category $category, Request $request): View
    {
        $data = $this->dataProvider->show($category, $request->all());

        return view('frontend.categories.show', $data);
    }
}
