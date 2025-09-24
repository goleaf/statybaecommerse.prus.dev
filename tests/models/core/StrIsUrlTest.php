<?php

declare(strict_types=1);

use Illuminate\Support\Str;

it('validates URLs correctly with Str::isUrl', function () {
    // Valid URLs
    expect(Str::isUrl('https://example.com'))->toBeTrue();
    expect(Str::isUrl('http://example.com'))->toBeTrue();
    expect(Str::isUrl('https://www.example.com'))->toBeTrue();
    expect(Str::isUrl('https://example.com/path'))->toBeTrue();
    expect(Str::isUrl('https://example.com/path?query=value'))->toBeTrue();
    expect(Str::isUrl('https://example.com/path#fragment'))->toBeTrue();
    expect(Str::isUrl('https://subdomain.example.com'))->toBeTrue();
    expect(Str::isUrl('https://example.com:8080'))->toBeTrue();

    // Invalid URLs
    expect(Str::isUrl('not-a-url'))->toBeFalse();
    expect(Str::isUrl('example.com'))->toBeFalse();
    expect(Str::isUrl(''))->toBeFalse();
    expect(Str::isUrl(' '))->toBeFalse();

    // FTP URLs are valid by default in Laravel's Str::isUrl
    expect(Str::isUrl('ftp://example.com'))->toBeTrue();
});

it('validates URLs with custom protocols', function () {
    // Valid with custom protocols
    expect(Str::isUrl('https://example.com', ['https']))->toBeTrue();
    expect(Str::isUrl('http://example.com', ['http']))->toBeTrue();
    expect(Str::isUrl('https://example.com', ['http', 'https']))->toBeTrue();

    // Invalid with custom protocols
    expect(Str::isUrl('http://example.com', ['https']))->toBeFalse();
    expect(Str::isUrl('https://example.com', ['http']))->toBeFalse();
    expect(Str::isUrl('ftp://example.com', ['http', 'https']))->toBeFalse();
});

it('validates URLs with custom protocols including ftp', function () {
    expect(Str::isUrl('ftp://example.com', ['ftp']))->toBeTrue();
    expect(Str::isUrl('ftp://example.com', ['http', 'https', 'ftp']))->toBeTrue();
    expect(Str::isUrl('https://example.com', ['http', 'https', 'ftp']))->toBeTrue();
});
