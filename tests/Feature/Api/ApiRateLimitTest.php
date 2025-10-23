<?php declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

final class ApiRateLimitTest extends RateLimitTestCase
{
    public function test_authenticated_user_endpoint_is_rate_limited(): void
    {
        config(['api.rate_limits.default' => 1]);

        $user = User::factory()->create();
        $this->clearRateLimit($user, '', 'api.default');
        Sanctum::actingAs($user, ['profile.read']);

        $this->saturateRateLimit($user, '', 'api.default');

        $this->getJson(route('api.v1.user.show'))
            ->assertStatus(429);
    }

    public function test_autocomplete_endpoint_is_rate_limited(): void
    {
        config(['api.rate_limits.autocomplete' => 1]);

        $user = User::factory()->create();
        User::factory()->create(['name' => 'Jane Example', 'email' => 'jane@example.com']);

        $this->clearRateLimit($user, 'autocomplete', 'api.autocomplete');
        Sanctum::actingAs($user, ['system.autocomplete']);

        $this->saturateRateLimit($user, 'autocomplete', 'api.autocomplete');

        $payload = [
            'model_class' => User::class,
            'search_field' => 'name',
            'label_field' => 'name',
            'value_field' => 'id',
            'search_query' => 'Jane',
        ];

        $this->postJson(route('api.v1.autocomplete.search'), $payload)
            ->assertStatus(429);
    }

    public function test_signed_export_endpoint_is_rate_limited(): void
    {
        config(['api.rate_limits.exports' => 1]);

        $user = User::factory()->create();

        $export = Export::factory()
            ->for($user, 'requestedBy')
            ->create([
                'status' => ExportStatus::Completed,
                'artifact_disk' => 'public',
                'artifact_path' => 'exports/example.csv',
                'artifact_filename' => 'example.csv',
                'requested_by' => $user->getKey(),
            ]);

        $this->clearRateLimit($user, 'exports', 'api.exports');
        Sanctum::actingAs($user, ['exports.download']);

        $this->saturateRateLimit($user, 'exports', 'api.exports');

        $signedUrl = URL::temporarySignedRoute(
            'exports.signed-download',
            now()->addMinute(),
            ['export' => $export],
        );

        $this->get($signedUrl)->assertStatus(429);
    }
}
