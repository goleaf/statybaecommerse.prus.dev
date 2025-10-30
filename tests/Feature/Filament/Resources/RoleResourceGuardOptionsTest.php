<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\RoleResource;
use App\Support\Authorization\AuthorizationMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RoleResourceGuardOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guard_options_expose_every_matrix_guard(): void
    {
        // Arrange: register an additional guard to mirror multi-context deployments.
        config(['authorization.guards' => ['admin', 'web', 'sanctum']]);

        // Act: resolve the guard options surfaced to the Filament role resource.
        $options = RoleResource::guardOptions();

        // Assert: confirm every guard from the matrix is represented as a selectable option.
        foreach (AuthorizationMatrix::guardNames() as $guard) {
            $this->assertArrayHasKey(
                $guard,
                $options,
                sprintf('Guard [%s] should be available to administrators configuring roles.', $guard)
            );
        }
    }

    public function test_default_guard_name_returns_first_configured_guard(): void
    {
        // Arrange: reorder the guard configuration to prefer sanctum-style authentication first.
        config(['authorization.guards' => ['sanctum', 'admin', 'web']]);

        // Act: ask the resource for its default guard name.
        $defaultGuard = RoleResource::defaultGuardName();

        // Assert: ensure the first configured guard becomes the default selection.
        $this->assertSame(
            'sanctum',
            $defaultGuard,
            'The default guard should match the leading guard from the configuration array.'
        );
    }
}
