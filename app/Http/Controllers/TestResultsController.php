<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TestResultsService;
use Illuminate\Contracts\View\View;

final class TestResultsController
{
    public function __invoke(TestResultsService $service): View
    {
        return view('pages.test-results', [
            'viewModel' => $service->buildViewModel(),
        ]);
    }
}
