<?php

declare(strict_types=1);

use App\Services\SearchInsightsService;
use Tests\TestCase;

uses(TestCase::class);

it('returns search insights with expected keys', function () {
    $svc = app(SearchInsightsService::class);
    $insights = $svc->getSearchInsights('best hammer', ['user_id' => 1]);

    expect($insights)->toHaveKeys([
        'query_analysis',
        'search_trends',
        'user_behavior',
        'performance_metrics',
        'recommendations',
        'related_searches',
        'search_suggestions',
    ]);
});
