<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\UserProfileController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_authenticated_user(): void
    {
        $controller = new UserProfileController();
        $request = Request::create('/user/profile', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(static fn () => null);

        try {
            $controller($request);
            $this->fail('Expected an HttpException to be thrown for unauthenticated access.');
        } catch (HttpException $exception) {
            // Assert guests receive a 401 response so private contract data remains protected.
            $this->assertSame(401, $exception->getStatusCode());
        }
    }

    public function test_profile_returns_not_found_for_trashed_user(): void
    {
        $controller = new UserProfileController();
        $user = User::factory()->create();
        $user->delete();
        $request = Request::create('/user/profile', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(static fn () => $user->fresh());

        try {
            $controller($request);
            $this->fail('Expected an HttpException to be thrown for trashed user access.');
        } catch (HttpException $exception) {
            // Assert soft-deleted accounts cannot access the profile contract to avoid leaking stale data.
            $this->assertSame(404, $exception->getStatusCode());
        }
    }

    public function test_profile_returns_contract_payload_for_active_user(): void
    {
        $controller = new UserProfileController();
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'User',
        ]);
        $request = Request::create('/user/profile', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(static fn () => $user);

        $response = $controller($request);

        // Assert the controller negotiates a JSON response for API consumers.
        $this->assertInstanceOf(JsonResponse::class, $response);
        $payload = $response->getData(true);
        // Assert the envelope references the user contract identifier for schema tracking.
        $this->assertSame('user', $payload['contract']);
        // Assert the embedded user id matches the authenticated principal for accuracy.
        $this->assertSame($user->getKey(), $payload['data']['id']);
    }
}
