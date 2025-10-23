<?php

declare(strict_types=1);

it('returns 404 for invalid encoded path', function (): void {
    $response = $this->getSignedRoute('media.secure-download', ['encodedPath' => '!!invalid!!']);
    $response->assertStatus(404);
});

it('serves file inline with correct headers', function (): void {
    $disk = config('media-security.disk', 'secure-media');
    $path = 'testing/hello.txt';
    Storage::disk($disk)->put($path, 'hi');

    $encoded = \App\Support\Storage\SecureStorage::encodePath($path);

    $response = $this->getSignedRoute('media.secure-download', ['encodedPath' => $encoded]);

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Content-Security-Policy');
    $response->assertHeader('Content-Disposition');
});

function getSignedRoute(string $name, array $params): \Illuminate\Testing\TestResponse
{
    $url = URL::temporarySignedRoute($name, now()->addMinutes(10), $params);

    return test()->get($url);
}
