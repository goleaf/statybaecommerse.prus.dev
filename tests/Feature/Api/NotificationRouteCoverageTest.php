<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

final class NotificationRouteCoverageTest extends TestCase
{
    public function test_every_notification_controller_action_has_a_named_route(): void
    {
        $routes = $this->notificationRoutes();
        $expectedMethods = $this->controllerMethods();

        $this->assertNotEmpty($routes, 'No routes were registered for NotificationController.');

        foreach ($expectedMethods as $method) {
            $this->assertArrayHasKey($method, $routes, sprintf('Missing route for %s::%s', NotificationController::class, $method));
            $this->assertNotEmpty($routes[$method], sprintf('Route for %s::%s must have a name.', NotificationController::class, $method));
            $this->assertStringStartsWith('api.v1.notifications.', $routes[$method]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function notificationRoutes(): array
    {
        return Collection::make(RouteFacade::getRoutes())
            ->filter(function (Route $route): bool {
                $action = $route->getActionName();

                return str_contains($action, NotificationController::class.'@');
            })
            ->mapWithKeys(function (Route $route): array {
                $action = $route->getActionName();
                [, $method] = explode('@', $action);

                return [$method => (string) $route->getName()];
            })
            ->toArray();
    }

    /**
     * @return array<int, string>
     */
    private function controllerMethods(): array
    {
        return Collection::make((new \ReflectionClass(NotificationController::class))->getMethods(\ReflectionMethod::IS_PUBLIC))
            // Filter ensures we only evaluate the controller's declared actions instead of inherited framework methods.
            ->filter(static fn (\ReflectionMethod $method): bool => $method->class === NotificationController::class && ! $method->isConstructor())
            ->map(static fn (\ReflectionMethod $method): string => $method->name)
            ->toArray();
    }
}
