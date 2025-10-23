<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\Frontend\DataProviders\HomePageDataProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class HomeController extends Controller
{
    public function __construct(private readonly HomepageCatalogueDataProvider $dataProvider) {}

    public function index(Request $request): View
    {
        return view('frontend.home.index', $this->dataProvider->get());
    }
}
