<?php

declare(strict_types=1);

use App\Rules\UrlRule;
use Illuminate\Support\Facades\Validator;

it('validates URLs correctly with UrlRule', function () {
    $rule = new UrlRule;

    // Valid URLs
    $validUrls = [
        'https://example.com',
        'http://example.com',
        'https://www.example.com',
        'https://example.com/path',
        'https://example.com/path?query=value',
        'https://example.com/path#fragment',
        'https://subdomain.example.com',
        'https://example.com:8080',
    ];

    foreach ($validUrls as $url) {
        $validator = Validator::make(['url' => $url], ['url' => $rule]);
        expect($validator->passes())->toBeTrue("Failed for URL: {$url}");
    }
});

it('rejects invalid URLs with UrlRule', function () {
    $rule = new UrlRule;

    // Invalid URLs (excluding empty strings which are handled separately)
    $invalidUrls = [
        'not-a-url',
        'example.com',
    ];

    foreach ($invalidUrls as $url) {
        $validator = Validator::make(['url' => $url], ['url' => $rule]);
        expect($validator->fails())->toBeTrue("Should have failed for URL: {$url}");
    }

    // FTP URLs are valid by default in Laravel's Str::isUrl
    $validator = Validator::make(['url' => 'ftp://example.com'], ['url' => $rule]);
    expect($validator->passes())->toBeTrue('FTP URLs should be valid by default');
});

it('validates URLs with custom protocols', function () {
    $rule = new UrlRule(['https']);

    // Valid with custom protocols
    expect(Validator::make(['url' => 'https://example.com'], ['url' => $rule])->passes())->toBeTrue();

    // Invalid with custom protocols
    expect(Validator::make(['url' => 'http://example.com'], ['url' => $rule])->fails())->toBeTrue();
    expect(Validator::make(['url' => 'ftp://example.com'], ['url' => $rule])->fails())->toBeTrue();
});

it('handles non-string values correctly', function () {
    $rule = new UrlRule;

    // Non-string values should fail
    $nonStringValues = [123, [], null, true, false];

    foreach ($nonStringValues as $value) {
        $validator = Validator::make(['url' => $value], ['url' => $rule]);
        expect($validator->fails())->toBeTrue('Should have failed for non-string value: '.gettype($value));
    }
});

it('handles empty strings correctly', function () {
    $rule = new UrlRule;

    // Empty strings should fail when required
    $validator = Validator::make(['url' => ''], ['url' => ['required', $rule]]);
    expect($validator->fails())->toBeTrue();

    // Whitespace-only strings should fail when required
    $validator = Validator::make(['url' => '   '], ['url' => ['required', $rule]]);
    expect($validator->fails())->toBeTrue();

    // Empty strings should pass when nullable (Laravel behavior)
    $validator = Validator::make(['url' => ''], ['url' => ['nullable', $rule]]);
    expect($validator->passes())->toBeTrue();
});
