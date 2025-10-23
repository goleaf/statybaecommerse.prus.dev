<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Support\Frontend\DataProviders\BrandCatalogueDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class BrandController extends Controller
{
    public function __construct(private readonly BrandCatalogueDataProvider $dataProvider)
    {
    }

    public function index(Request $request): View
    {
        $data = $this->dataProvider->index();

        return view('frontend.brands.index', $data);
    }

    public function show(Brand $brand, Request $request): View
    {
        $data = $this->dataProvider->show($brand, $request->all());

        return view('frontend.brands.show', $data);
    }
}
