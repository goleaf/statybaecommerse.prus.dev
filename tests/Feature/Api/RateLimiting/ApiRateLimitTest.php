<?php

declare(strict_types=1);

namespace Tests\Feature\Api\RateLimiting;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\InteractsWithRateLimitSchema;
use Tests\TestCase;

final class ApiRateLimitTest extends TestCase
{
    use InteractsWithRateLimitSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRateLimitSchema();
    }

    protected function tearDown(): void
    {
        $this->tearDownRateLimitSchema();

        parent::tearDown();
    }

    public function test_user_endpoint_enforces_rate_limit(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user, ['profile.read']);

        $originalLimit = config('security.rate_limiting.api.default');
        config(['security.rate_limiting.api.default' => 1]);

        $rateKey = 'user:' . $user->id;

        try {
            $this->clearRateLimiterKeys('api.default', $rateKey);

            $this->getJson(route('api.v1.user.show'))->assertOk();
            $this->getJson(route('api.v1.user.show'))->assertStatus(429);
        } finally {
            config(['security.rate_limiting.api.default' => $originalLimit]);
            $this->clearRateLimiterKeys('api.default', $rateKey);
        }
    }

    public function test_autocomplete_endpoint_respects_dedicated_rate_limit(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user, ['system.autocomplete']);

        $originalLimit = config('security.rate_limiting.api.autocomplete');
        config(['security.rate_limiting.api.autocomplete' => 1]);

        $rateKey = 'user:' . $user->id . '|autocomplete';
        $payload = [
            'model_class' => User::class,
            'search_query' => $user->email,
            'label_field' => 'email',
            'value_field' => 'id',
        ];

        try {
            $this->clearRateLimiterKeys('api.autocomplete', $rateKey);

            $this->postJson(route('api.v1.autocomplete.search'), $payload)->assertOk();
            $this->postJson(route('api.v1.autocomplete.search'), $payload)->assertStatus(429);
        } finally {
            config(['security.rate_limiting.api.autocomplete' => $originalLimit]);
            $this->clearRateLimiterKeys('api.autocomplete', $rateKey);
        }
    }

    public function test_signed_export_download_requires_auth_and_ability_and_rate_limits(): void
    {
        Storage::fake('secure-media');

        $user = $this->createUser();

        $export = Export::create([
            'name' => 'Orders export',
            'format' => 'csv',
            'status' => ExportStatus::Completed,
            'artifact_disk' => 'secure-media',
            'artifact_path' => 'exports/orders.csv',
            'artifact_filename' => 'orders.csv',
            'requested_by' => $user->id,
        ]);

        Storage::disk('secure-media')->put($export->artifact_path, 'report');

        $signedUrl = URL::signedRoute('exports.signed-download', ['export' => $export]);

        $originalLimit = config('security.rate_limiting.api.exports');
        $ipKey = 'ip:127.0.0.1|exports';
        $userKey = 'user:' . $user->id . '|exports';

        try {
            config(['security.rate_limiting.api.exports' => 10]);
            $this->clearRateLimiterKeys('api.exports', $ipKey, $userKey);

            $this->get($signedUrl)->assertUnauthorized();

            Sanctum::actingAs($user, ['profile.read']);
            $this->get($signedUrl)->assertForbidden();

            config(['security.rate_limiting.api.exports' => 1]);
            $this->clearRateLimiterKeys('api.exports', $ipKey, $userKey);

            Sanctum::actingAs($user, ['exports.download']);
            $this->get($signedUrl)->assertOk();
            $this->get($signedUrl)->assertStatus(429);
        } finally {
            config(['security.rate_limiting.api.exports' => $originalLimit]);
            $this->clearRateLimiterKeys('api.exports', $ipKey, $userKey);
        }
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => Str::uuid()->toString() . '@example.com',
            'password' => 'password',
            'preferred_locale' => 'en',
            'email_verified_at' => now(),
        ]);
    }
}
