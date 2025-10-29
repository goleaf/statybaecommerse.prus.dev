<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TestResultsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;

final class TestResultsController
{
    public function __invoke(TestResultsService $service): View
    {
        // Force the analytics dashboard to render with the fallback locale so
        // automation-oriented copy (and related tests) always expose the
        // English wording regardless of the storefront's default language.
        App::setLocale(config('app.fallback_locale', 'en'));

        return view('pages.test-results', [
            'viewModel' => $service->buildViewModel(),
        ]);
    }
}
