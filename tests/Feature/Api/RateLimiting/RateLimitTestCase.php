<?php

declare(strict_types=1);

namespace Tests\Feature\Api\RateLimiting;

use App\Models\User;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use LogicException;
use Tests\Feature\Api\RateLimiting\Concerns\InteractsWithRateLimitSchema;
use Tests\TestCase;

abstract class RateLimitTestCase extends TestCase
{
    use InteractsWithRateLimitSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateRateLimitSchema();

        try {
            Gate::define('exports.view', static fn (): bool => true);
        } catch (LogicException) {
            // Ability already registered for the test run.
        }

        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('abilities', CheckAbilities::class);
        $router->aliasMiddleware('ability', CheckForAnyAbility::class);
    }

    protected function tearDown(): void
    {
        $this->resetRateLimitSchema();

        parent::tearDown();
    }

    protected function makeSanctumUser(int $id, array $overrides = []): User
    {
        $attributes = array_merge([
            'id'                => $id,
            'name'              => 'Rate Limit User ' . $id,
            'email'             => sprintf('rate-limit-user-%d@example.com', $id),
            'is_admin'          => true,
            'email_verified_at' => now(),
            'preferred_locale'  => 'en',
        ], $overrides);

        $user = new User;
        $user->forceFill($attributes);
        $user->exists = true;

        return $user;
    }
}
