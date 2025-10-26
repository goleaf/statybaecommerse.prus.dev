<?php

declare(strict_types=1);

it('returns 404 for invalid encoded path', function (): void {
    // Invalid base64 payloads must be rejected early to avoid filesystem lookups.
    $response = getSignedRoute('media.secure-download', ['encodedPath' => '!!invalid!!']);
    $response->assertStatus(404);
});

it('serves file inline with correct headers', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    $path = 'testing/hello.txt';
    Storage::disk($disk)->put($path, 'hi');

    $encoded = \App\Support\Storage\SecureStorage::encodePath($path);

    $response = getSignedRoute('media.secure-download', ['encodedPath' => $encoded]);

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('Content-Disposition');

    // Ensure we do not accidentally inject attachment headers when not requested.
    expect($response->headers->get('Content-Disposition'))->toStartWith('inline;');

    Storage::disk($disk)->delete($path);
});

it('forces attachment downloads when requested', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    $path = 'testing/download.txt';
    Storage::disk($disk)->put($path, 'download me');

    $encoded = \App\Support\Storage\SecureStorage::encodePath($path);

    // Force the download parameter to check attachment disposition handling.
    $response = getSignedRoute('media.secure-download', [
        'encodedPath' => $encoded,
        'download'    => '1',
    ]);

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toStartWith('attachment;');

    Storage::disk($disk)->delete($path);
});

it('sanitizes potentially dangerous filenames', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    $path = 'testing/unsafe"file-name.txt';
    Storage::disk($disk)->put($path, 'secure');

    $encoded = \App\Support\Storage\SecureStorage::encodePath($path);

    // Trigger inline serving to inspect the sanitized Content-Disposition header.
    $response = getSignedRoute('media.secure-download', ['encodedPath' => $encoded]);

    $response->assertOk();
    $header = $response->headers->get('Content-Disposition');
    expect($header)->toStartWith('inline;');
    expect($header)->not()->toContain('"file');

    Storage::disk($disk)->delete($path);
});

function getSignedRoute(string $name, array $params): \Illuminate\Testing\TestResponse
{
    $url = URL::temporarySignedRoute($name, now()->addMinutes(10), $params);

    return test()->get($url);
}
