<?php

declare(strict_types=1);

use App\Services\SearchHighlightingService;
use Tests\TestCase;

uses(TestCase::class);

it('highlights search terms in text', function () {
    $svc = app(SearchHighlightingService::class);
    $res = $svc->highlightSearchTerms('Hello world, hello Laravel', 'hello');
    expect($res)->toContain('<mark class="search-highlight">hello</mark>');
});

it('creates snippets around terms', function () {
    $svc = app(SearchHighlightingService::class);
    $snippet = $svc->createSnippet('Laravel makes PHP delightful. Searching with terms should clip.', 'delightful', 40);
    expect($snippet)->toBeString()->not->toBe('');
});
