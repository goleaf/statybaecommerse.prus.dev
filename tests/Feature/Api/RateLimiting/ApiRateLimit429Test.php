<?php

declare(strict_types=1);

namespace Tests\Feature\Api\RateLimiting;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

final class ApiRateLimit429Test extends RateLimitTestCase
{
    public function test_user_endpoint_returns_429_when_limit_is_exceeded(): void
    {
        config()->set('security.rate_limiting.api.default', 1);

        $user = $this->makeSanctumUser(101);
        Sanctum::actingAs($user, ['profile.read'], 'sanctum');

        $ipKey = 'ip:127.0.0.1';
        $userKey = 'user:'.$user->getAuthIdentifier();
        RateLimiter::clear($ipKey);
        RateLimiter::clear($userKey);

        $first = $this->getJson('/api/v1/user');
        $first->assertOk();

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(429);
    }

    public function test_autocomplete_endpoint_returns_429_when_limit_is_exceeded(): void
    {
        config()->set('security.rate_limiting.api.autocomplete', 1);

        $user = $this->makeSanctumUser(202);
        Sanctum::actingAs($user, ['profile.read', 'system.autocomplete'], 'sanctum');

        $key = 'user:'.$user->getAuthIdentifier().'|autocomplete';
        RateLimiter::clear($key);

        $payload = [
            'model_class' => User::class,
            'search_query' => 'demo',
        ];

        $this->postJson('/api/v1/autocomplete-search', $payload)->assertOk();

        $response = $this->postJson('/api/v1/autocomplete-search', $payload);

        $response->assertStatus(429);
    }

    public function test_signed_export_download_returns_429_when_limit_is_exceeded(): void
    {
        config()->set('security.rate_limiting.api.exports', 1);
        config()->set('media-security.disk', 'secure-media');

        Storage::fake('secure-media');

        $export = $this->createCompletedExport();

        Storage::disk('secure-media')->put($export->artifact_path, 'id,name');

        $ipKey = 'ip:127.0.0.1|exports';
        RateLimiter::clear($ipKey);

        $this->get($this->signedDownloadUrl($export))->assertOk();

        $response = $this->get($this->signedDownloadUrl($export));

        $response->assertStatus(429);

        RateLimiter::clear($ipKey);
    }

    private function createCompletedExport(): Export
    {
        return Export::unguarded(function (): Export {
            /** @var Export $export */
            $export = Export::query()->create([
                'uuid' => (string) Str::uuid(),
                'name' => 'Rate Limit Export',
                'format' => 'csv',
                'status' => ExportStatus::Completed,
                'artifact_disk' => 'secure-media',
                'artifact_path' => 'exports/rate-limit.csv',
                'artifact_filename' => 'rate-limit.csv',
            ]);

            return $export;
        });
    }

    private function signedDownloadUrl(Export $export): string
    {
        return URL::signedRoute('api.exports.download', ['export' => $export]);
    }
}
