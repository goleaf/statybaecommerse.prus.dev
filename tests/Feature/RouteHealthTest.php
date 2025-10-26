<?php

declare(strict_types=1);

use App\Models\AdminUser;
use App\Models\User;
use App\Support\RouteAudit\PayloadFactory;
use App\Support\RouteAudit\StaticAnalyzer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

beforeAll(function (): void {
    Artisan::call('migrate:fresh', [
        '--database' => 'sqlite',
        '--seed'     => true,
    ]);
});

/**
 * Route health checks iterate over every defined route and verify both static and dynamic aspects.
 * Static coverage focuses on structure (names, controller methods, middleware aliases) while dynamic
 * coverage performs HTTP calls as guest and authenticated contexts to surface runtime failures.
 */
it('probes routes for runtime failures', function (): void {
    $staticAnalyzer = new StaticAnalyzer;
    $payloadFactory = new PayloadFactory;
    $staticReport = $staticAnalyzer->analyze();

    /** @var Router $router */
    $router = app(Router::class);

    $routeMap = [];
    foreach ($router->getRoutes() as $route) {
        $fingerprint = fingerprint_route($route);
        $routeMap[$fingerprint] = $route;
    }

    $results = [];
    $failures = [];

    foreach ($staticReport['routes'] as $entry) {
        $fingerprint = $entry['fingerprint'];

        if (! isset($routeMap[$fingerprint])) {
            $results[] = [
                'fingerprint' => $fingerprint,
                'uri'         => $entry['uri'],
                'name'        => $entry['name'],
                'status'      => 'failed',
                'error'       => 'Route object not found for fingerprint.',
                'guest'       => null,
                'auth'        => null,
            ];
            $failures[] = sprintf('Unable to resolve route for %s %s', implode(',', $entry['methods']), $entry['uri']);

            continue;
        }

        /** @var Route $route */
        $route = $routeMap[$fingerprint];

        if ($entry['skipDynamic']) {
            $results[] = [
                'fingerprint' => $fingerprint,
                'uri'         => $entry['uri'],
                'name'        => $entry['name'],
                'status'      => 'skipped',
                'guest'       => null,
                'auth'        => null,
                'notes'       => 'Route excluded from dynamic checks.',
            ];

            continue;
        }

        $params = build_route_parameters($entry, $route);
        $headers = headers_for_route($entry);
        $payload = $payloadFactory->build($entry);

        $guestResults = probe_route($this, $route, $entry, $params, $payload, $headers, guard: null);
        $authResults = probe_route($this, $route, $entry, $params, $payload, $headers, guard: $entry['middlewares']['auth'] ?? null);

        $status = 'passed';
        $errorMessage = null;

        foreach ([$guestResults, $authResults] as $result) {
            if (($result['status'] ?? 200) >= 500) {
                $status = 'failed';
                $errorMessage = $result['error'] ?? 'Server error response.';
            }
        }

        if ($status === 'failed') {
            $failures[] = sprintf(
                'Route %s %s returned %s (%s)',
                implode(',', $entry['methods']),
                $entry['uri'],
                $guestResults['status'] ?? $authResults['status'] ?? '500',
                $errorMessage ?? 'error'
            );
        }

        $results[] = [
            'fingerprint' => $fingerprint,
            'uri'         => $entry['uri'],
            'name'        => $entry['name'],
            'status'      => $status,
            'error'       => $errorMessage,
            'guest'       => $guestResults,
            'auth'        => $authResults,
        ];
    }

    $report = [
        'generatedAt' => now()->toIso8601String(),
        'routes'      => $results,
    ];

    File::ensureDirectoryExists(storage_path('app/route_audit'));
    File::put(
        storage_path('app/route_audit/dynamic_results.json'),
        json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}'
    );

    expect($failures)->toBeEmpty();
});

it('ensures route names are unique', function (): void {
    $names = [];
    foreach (RouteFacade::getRoutes() as $route) {
        $name = $route->getName();
        if (! $name) {
            continue;
        }
        $names[] = $name;
    }

    $duplicates = array_diff_assoc($names, array_unique($names));

    expect($duplicates)->toBeEmpty();
});

it('ensures controller methods are public and resolvable', function (): void {
    $invalid = [];

    foreach (RouteFacade::getRoutes() as $route) {
        $action = $route->getAction()['uses'] ?? null;
        if ($action instanceof Closure || $action === null) {
            continue;
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action);
        } else {
            $class = is_array($action) ? $action[0] : $action;
            $method = is_array($action) ? $action[1] : '__invoke';
        }

        if (! is_string($class) || $class === '') {
            continue;
        }

        if (! class_exists($class)) {
            $invalid[] = sprintf('%s controller missing', $class);

            continue;
        }

        try {
            $reflection = new ReflectionClass($class);
            if (! $reflection->hasMethod($method)) {
                $invalid[] = sprintf('%s::%s missing', $class, $method);

                continue;
            }

            if (! $reflection->getMethod($method)->isPublic()) {
                $invalid[] = sprintf('%s::%s not public', $class, $method);
            }
        } catch (ReflectionException $exception) {
            $invalid[] = $exception->getMessage();
        }
    }

    expect($invalid)->toBeEmpty();
});

it('ensures middleware aliases resolve', function (): void {
    $router = app(Router::class);
    $aliases = $router->getMiddleware();
    $groups = $router->getMiddlewareGroups();
    $errors = [];

    foreach (RouteFacade::getRoutes() as $route) {
        foreach ($route->middleware() as $middleware) {
            $alias = explode(':', $middleware, 2)[0];

            if (isset($aliases[$alias]) || isset($groups[$alias]) || class_exists($alias)) {
                continue;
            }

            $errors[] = $alias;
        }
    }

    expect($errors)->toBeEmpty();
});

it('ensures gates exist for can middleware', function (): void {
    $missing = [];

    foreach (RouteFacade::getRoutes() as $route) {
        foreach ($route->middleware() as $middleware) {
            if (! str_starts_with($middleware, 'can:')) {
                continue;
            }

            $ability = explode(',', substr($middleware, 4))[0] ?? null;
            if (! $ability) {
                continue;
            }

            if (! Gate::has($ability)) {
                $missing[] = $ability;
            }
        }
    }

    expect($missing)->toBeEmpty();
});

/**
 * Build a consistent fingerprint for a route using the same strategy as the static analyser.
 */
function fingerprint_route(Route $route): string
{
    $name = $route->getName() ?? '';
    $methods = implode('|', array_diff($route->methods(), ['HEAD']));

    return hash('sha1', $name . '|' . $route->uri() . '|' . $methods);
}

/**
 * Generate route parameter values leveraging form requests and model factories where available.
 *
 * @param  array<string, mixed> $entry
 * @return array<string, mixed>
 */
function build_route_parameters(array $entry, Route $route): array
{
    $parameters = [];

    foreach ($entry['parameters'] as $parameter) {
        $name = $parameter['name'];
        $bindingClass = $parameter['bindingType'] ?? null;
        $isRequired = $parameter['required'] ?? false;

        if ($bindingClass && class_exists($bindingClass)) {
            $model = resolve_model_instance($bindingClass);
            if ($model) {
                $parameters[$name] = $model->getRouteKey();

                continue;
            }
        }

        if (! $isRequired) {
            continue;
        }

        $constraint = $parameter['constraint'] ?? null;

        if (is_string($constraint) && preg_match('/\\d+/', $constraint)) {
            $parameters[$name] = '1';

            continue;
        }

        $parameters[$name] = 'sample';
    }

    return $parameters;
}

/**
 * Attempt to resolve a model instance for implicit bindings.
 *
 * @param class-string<Model> $class
 */
function resolve_model_instance(string $class): ?Model
{
    if (! class_exists($class)) {
        return null;
    }

    $model = new $class;

    if (method_exists($class, 'factory')) {
        try {
            return $class::factory()->create();
        } catch (Throwable) {
            // fall through
        }
    }

    return $model->newQuery()->first() ?? null;
}

/**
 * Resolve default headers for a route.
 *
 * @param  array<string, mixed>  $entry
 * @return array<string, string>
 */
function headers_for_route(array $entry): array
{
    $headers = [
        'Accept' => 'text/html,application/xhtml+xml,application/json',
    ];

    if (in_array('api', $entry['middlewares']['declared'], true)) {
        $headers['Accept'] = 'application/json';
    }

    return $headers;
}

/**
 * Probe a route either as guest or authenticated context, returning the collected metadata.
 *
 * @param  array<string, mixed>  $entry
 * @param  array<string, mixed>  $params
 * @param  array<string, mixed>  $payload
 * @param  array<string, string> $headers
 * @return array<string, mixed>
 */
function probe_route(
    TestCase $test,
    Route $route,
    array $entry,
    array $params,
    array $payload,
    array $headers,
    ?string $guard
): array {
    $methods = $entry['methods'];

    $contextUser = null;
    $contextGuard = $guard ?: null;

    if ($contextGuard !== null) {
        $contextUser = authenticate_for_guard($test, $contextGuard);
    }

    $results = [
        'status' => null,
    ];

    foreach ($methods as $httpMethod) {
        $methodPayload = in_array($httpMethod, ['POST', 'PUT', 'PATCH', 'DELETE'], true) ? $payload : [];
        $response = send_request(
            $test,
            $route,
            $entry,
            $params,
            $methodPayload,
            $headers,
            $httpMethod,
            $contextUser,
            $contextGuard
        );

        $status = $response->getStatusCode();
        if ($results['status'] === null || $status > $results['status']) {
            $results['status'] = $status;
        }

        if ($status >= 500) {
            $results['error'] = sprintf('HTTP %s returned %d', $httpMethod, $status);
        } elseif ($status === 422) {
            $results['warning'] = 'Validation error';
        }

        if ($httpMethod === 'GET') {
            $headResponse = send_request(
                $test,
                $route,
                $entry,
                $params,
                [],
                $headers,
                'HEAD',
                $contextUser,
                $contextGuard
            );

            if (($headStatus = $headResponse->getStatusCode()) >= 500) {
                $results['status'] = $headStatus;
                $results['error'] = sprintf('HEAD check returned %d', $headStatus);
            }
        }
    }

    return $results;
}

/**
 * Send a request to the route under test with optional authentication context.
 *
 * @param array<string, mixed>  $entry
 * @param array<string, mixed>  $params
 * @param array<string, mixed>  $payload
 * @param array<string, string> $headers
 */
function send_request(
    TestCase $test,
    Route $route,
    array $entry,
    array $params,
    array $payload,
    array $headers,
    string $method,
    ?Authenticatable $user,
    ?string $guard
): TestResponse {
    $uri = $route->uri();

    if ($entry['signed'] && $entry['name']) {
        $url = URL::temporarySignedRoute($entry['name'], now()->addMinutes(5), $params);
        $path = parse_url($url, PHP_URL_PATH) ?: '/' . ltrim($uri, '/');
    } else {
        $path = '/' . ltrim($uri, '/');
    }

    if (! empty($params)) {
        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', (string) $value, $path);
            $path = str_replace('{' . $key . '?}', (string) $value, $path);
        }
    }

    $request = $test->withHeaders($headers);

    if ($user !== null) {
        if ($guard === 'sanctum') {
            Sanctum::actingAs($user, permissions: ['*']);
        } else {
            $request = $request->actingAs($user, $guard);
        }
    }

    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && in_array('web', $entry['middlewares']['declared'], true)) {
        $token = csrf_token();
        $payload['_token'] = $token;
        $request = $request->withSession(['_token' => $token]);
    }

    return $request->call($method, $path, $payload);
}

/**
 * Authenticate a user for the given guard.
 */
function authenticate_for_guard(TestCase $test, string $guard): Authenticatable
{
    switch ($guard) {
        case 'admin':
            $user = AdminUser::factory()->create();
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('super_admin');
                } catch (Throwable) {
                    // ignore if role missing
                }
            }
            $test->actingAs($user, 'admin');

            return $user;
        case 'sanctum':
        case 'api':
            $user = User::factory()->create(['is_admin' => true]);
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('administrator');
                } catch (Throwable) {
                    // ignore
                }
            }
            Sanctum::actingAs($user, permissions: ['*']);

            return $user;
        default:
            $user = User::factory()->create(['is_admin' => true]);
            if (method_exists($user, 'assignRole')) {
                try {
                    $user->assignRole('administrator');
                } catch (Throwable) {
                    // ignore
                }
            }
            $test->actingAs($user, 'web');

            return $user;
    }
}
