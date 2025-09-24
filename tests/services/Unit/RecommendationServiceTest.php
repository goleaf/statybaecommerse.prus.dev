<?php

declare(strict_types=1);

use App\Services\RecommendationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

it('returns recommendation blocks collection (smoke)', function () {
    if (! Schema::hasTable('recommendation_blocks')) {
        test()->markTestSkipped('recommendation_blocks table missing');
    }

    $svc = app(RecommendationService::class);
    $blocks = $svc->getRecommendationBlocks();
    expect($blocks)->toBeInstanceOf(Collection::class);
});
