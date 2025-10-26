<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;

final class StaticAnalyzer
{
    private Router $router;

    private RouteFilter $routeFilter;

    public function __construct(?Router $router = null, ?RouteFilter $routeFilter = null)
    {
        $this->router = $router ?? App::make(Router::class);
        $this->routeFilter = $routeFilter ?? new RouteFilter;
    }

    /**
     * Run the static route analysis and return a structured payload ready for downstream reporting.
     *
     * @return array{
     *     generatedAt: string,
     *     laravelVersion: string,
     *     routeCount: int,
     *     errors: int,
     *     warnings: int,
     *     routes: list<array<string, mixed>>
     * }
     */
    public function analyze(): array
    {
        /** @var Application $app */
        $app = App::getFacadeApplication();

        $routes = collect($this->router->getRoutes());

        $policies = Gate::policies();
        $middlewareAliases = $this->router->getMiddleware();
        $middlewareGroups = $this->router->getMiddlewareGroups();

        $entries = [];
        foreach ($routes as $route) {
            $entries[] = $this->inspectRoute(
                $route,
                $middlewareAliases,
                $middlewareGroups,
                $policies
            );
        }

        $entries = $this->applyDuplicateNameChecks($entries);

        $errorCount = 0;
        $warningCount = 0;

        foreach ($entries as $entry) {
            foreach ($entry['staticIssues'] as $issue) {
                if (($issue['severity'] ?? '') === Issue::SEVERITY_ERROR) {
                    $errorCount++;
                }
                if (($issue['severity'] ?? '') === Issue::SEVERITY_WARNING) {
                    $warningCount++;
                }
            }
        }

        usort($entries, static function (array $a, array $b): int {
            if ($a['skipDynamic'] !== $b['skipDynamic']) {
                return $a['skipDynamic'] ? 1 : -1;
            }

            return strcmp((string) $a['uri'], (string) $b['uri']);
        });

        return [
            'generatedAt'    => now()->toIso8601String(),
            'laravelVersion' => $app->version(),
            'routeCount'     => count($entries),
            'errors'         => $errorCount,
            'warnings'       => $warningCount,
            'routes'         => array_values($entries),
        ];
    }

    /**
     * @param  array<string, class-string>       $middlewareAliases
     * @param  array<string, array<int, string>> $middlewareGroups
     * @param  array<class-string, class-string> $policies
     * @return array<string, mixed>
     */
    private function inspectRoute(
        Route $route,
        array $middlewareAliases,
        array $middlewareGroups,
        array $policies
    ): array {
        $issues = [];
        $action = $route->getAction();
        $name = $route->getName() ?? '';

        $controller = $this->resolveController($route);
        $fingerprint = $this->fingerprint($route);

        if ($controller['isClosure']) {
            $actionString = 'Closure';
        } elseif ($controller['class'] !== null) {
            $actionString = $controller['class'] . '@' . $controller['method'];
        } else {
            $actionString = (string) Arr::get($action, 'uses', 'Closure');
        }

        if ($controller['class'] !== null && ! $controller['classExists']) {
            $issues[] = Issue::error('Controller class could not be autoloaded.', [
                'class' => $controller['class'],
            ]);
        }

        if ($controller['classExists'] && $controller['method'] !== null && ! $controller['methodExists']) {
            $issues[] = Issue::error('Controller method is missing or inaccessible.', [
                'class'  => $controller['class'],
                'method' => $controller['method'],
            ], 'Confirm the method name and visibility match the route definition.');
        }

        if ($controller['reflection'] instanceof ReflectionMethod && ! $controller['reflection']->isPublic()) {
            $issues[] = Issue::error('Controller method is not public.', [
                'class'  => $controller['class'],
                'method' => $controller['method'],
            ]);
        }

        if ($controller['reflection'] instanceof ReflectionMethod && $controller['reflection']->isStatic()) {
            $issues[] = Issue::warning('Controller method is static which may break dependency injection.', [
                'class'  => $controller['class'],
                'method' => $controller['method'],
            ]);
        }

        $middlewareInfo = $this->resolveMiddleware($route, $middlewareAliases, $middlewareGroups);
        $issues = array_merge($issues, $middlewareInfo['issues']);

        $parameterInfo = $this->inspectParameters($route, $controller['reflection'], $issues, $policies);
        $issues = $parameterInfo['issues'];

        $signed = in_array('signed', $middlewareInfo['declared'], true);
        $authMiddleware = $middlewareInfo['authGuard'];

        $entry = [
            'fingerprint' => $fingerprint,
            'uri'         => $route->uri(),
            'methods'     => array_values(array_unique(array_filter($route->methods(), static fn ($method) => $method !== 'HEAD'))),
            'name'        => $name,
            'action'      => $actionString,
            'controller'  => [
                'class'        => $controller['class'],
                'method'       => $controller['method'],
                'isInvokable'  => $controller['isInvokable'],
                'isClosure'    => $controller['isClosure'],
                'formRequests' => $controller['formRequests'],
                'parameters'   => $parameterInfo['controllerParameters'],
            ],
            'middlewares' => [
                'declared' => $middlewareInfo['declared'],
                'resolved' => $middlewareInfo['resolved'],
                'auth'     => $authMiddleware,
                'throttle' => $middlewareInfo['throttle'],
            ],
            'domain'       => $route->domain(),
            'parameters'   => $parameterInfo['routeParameters'],
            'where'        => $parameterInfo['constraints'],
            'policies'     => $parameterInfo['policyHints'],
            'signed'       => $signed,
            'skipDynamic'  => $this->routeFilter->shouldIgnore($route),
            'staticIssues' => $issues,
            'notes'        => '',
        ];

        if ($authMiddleware === null && Str::startsWith($route->uri(), 'api/')) {
            $entry['notes'] = trim($entry['notes'] . ' Possible public API route detected.');
        }

        return $entry;
    }

    private function fingerprint(Route $route): string
    {
        $name = $route->getName() ?? '';
        $methods = implode('|', array_diff($route->methods(), ['HEAD']));

        return hash('sha1', $name . '|' . $route->uri() . '|' . $methods);
    }

    /**
     * @return array{
     *   class: ?class-string,
     *   method: ?string,
     *   isInvokable: bool,
     *   isClosure: bool,
     *   classExists: bool,
     *   methodExists: bool,
     *   formRequests: list<class-string<FormRequest>>,
     *   reflection: ReflectionMethod|null
     * }
     */
    private function resolveController(Route $route): array
    {
        $uses = $route->getAction()['uses'] ?? null;

        if ($uses instanceof Closure) {
            return [
                'class'        => null,
                'method'       => null,
                'isInvokable'  => false,
                'isClosure'    => true,
                'classExists'  => true,
                'methodExists' => true,
                'formRequests' => [],
                'reflection'   => null,
            ];
        }

        $controllerClass = null;
        $controllerMethod = null;

        if (is_string($uses)) {
            if (str_contains($uses, '@')) {
                [$controllerClass, $controllerMethod] = explode('@', $uses, 2);
            } else {
                $controllerClass = $uses;
                $controllerMethod = '__invoke';
            }
        } elseif (is_array($uses) && count($uses) === 2 && is_string($uses[0])) {
            $controllerClass = $uses[0];
            $controllerMethod = is_string($uses[1]) ? $uses[1] : '__invoke';
        } else {
            $controllerClass = is_string($route->getActionName()) ? $route->getActionName() : null;
            if ($controllerClass !== null && ! str_contains($controllerClass, '@')) {
                $controllerMethod = '__invoke';
            }
        }

        $classExists = $controllerClass !== null && class_exists($controllerClass);

        if (! $classExists) {
            return [
                'class'        => $controllerClass,
                'method'       => $controllerMethod,
                'isInvokable'  => $controllerMethod === '__invoke',
                'isClosure'    => false,
                'classExists'  => false,
                'methodExists' => false,
                'formRequests' => [],
                'reflection'   => null,
            ];
        }

        try {
            $reflection = new ReflectionClass($controllerClass);
            $methodName = $controllerMethod ?? '__invoke';

            if (! $reflection->hasMethod($methodName)) {
                return [
                    'class'        => $controllerClass,
                    'method'       => $methodName,
                    'isInvokable'  => $methodName === '__invoke',
                    'isClosure'    => false,
                    'classExists'  => true,
                    'methodExists' => false,
                    'formRequests' => [],
                    'reflection'   => null,
                ];
            }

            $method = $reflection->getMethod($methodName);

            $formRequests = [];
            foreach ($method->getParameters() as $parameter) {
                $type = $parameter->getType();
                if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                    continue;
                }

                $typeName = $type->getName();
                if (is_subclass_of($typeName, FormRequest::class)) {
                    $formRequests[] = $typeName;
                }
            }

            return [
                'class'        => $controllerClass,
                'method'       => $method->getName(),
                'isInvokable'  => $method->getName() === '__invoke',
                'isClosure'    => false,
                'classExists'  => true,
                'methodExists' => true,
                'formRequests' => array_values(array_unique($formRequests)),
                'reflection'   => $method,
            ];
        } catch (ReflectionException) {
            return [
                'class'        => $controllerClass,
                'method'       => $controllerMethod,
                'isInvokable'  => $controllerMethod === '__invoke',
                'isClosure'    => false,
                'classExists'  => true,
                'methodExists' => false,
                'formRequests' => [],
                'reflection'   => null,
            ];
        }
    }

    /**
     * @param array<string, class-string>       $middlewareAliases
     * @param array<string, array<int, string>> $middlewareGroups
     * @return array{
     *   declared: list<string>,
     *   resolved: list<string>,
     *   authGuard: ?string,
     *   throttle: list<string>,
     *   issues: list<array<string, mixed>>
     * }
     */
    private function resolveMiddleware(Route $route, array $middlewareAliases, array $middlewareGroups): array
    {
        $issues = [];
        $declared = array_values(array_unique($route->middleware()));
        $resolved = $route->gatherMiddleware();

        $authGuard = null;
        $throttle = [];

        foreach ($declared as $name) {
            $parts = explode(':', $name, 2);
            $middlewareName = $parts[0];
            $parameterPayload = $parts[1] ?? null;

            if ($middlewareName === 'auth') {
                $authGuard = $parameterPayload ?? 'web';
            } elseif (str_starts_with($middlewareName, 'auth:')) {
                $authGuard = substr($middlewareName, 5);
            } elseif ($middlewareName === 'auth.basic') {
                $authGuard = 'basic';
            }

            if ($middlewareName === 'throttle') {
                $throttle[] = $parameterPayload ?? 'global';
                if ($parameterPayload !== null) {
                    $limiterName = $this->extractThrottleLimiterName($parameterPayload);
                    if ($limiterName !== null && RateLimiter::limiter($limiterName) === null) {
                        $issues[] = Issue::error('Throttle middleware references unknown rate limiter.', [
                            'middleware' => $name,
                            'limiter'    => $limiterName,
                        ], 'Register the limiter in a service provider using RateLimiter::for().');
                    }
                }
            }

            if (! $this->middlewareExists($middlewareName, $middlewareAliases, $middlewareGroups, $resolved)) {
                $issues[] = Issue::error('Middleware alias is not registered.', [
                    'middleware' => $middlewareName,
                ], 'Ensure the alias is defined via Application::withMiddleware() or the HTTP kernel.');
            }

            if ($middlewareName === 'can' && $parameterPayload !== null) {
                $ability = explode(',', $parameterPayload)[0];
                if (! Gate::has($ability)) {
                    $issues[] = Issue::error('Route references unknown gate or policy ability.', [
                        'ability'    => $ability,
                        'middleware' => $name,
                    ], 'Define the ability within AuthServiceProvider or a dedicated policy.');
                }
            }

            if (in_array($middlewareName, ['permission', 'permissions'], true)) {
                if (! class_exists(\Spatie\Permission\Models\Permission::class)) {
                    $issues[] = Issue::warning('Permission middleware is present but spatie/laravel-permission models not found.', [
                        'middleware' => $name,
                    ]);
                }
            }
        }

        return [
            'declared'  => $declared,
            'resolved'  => array_values(array_unique($resolved)),
            'authGuard' => $authGuard,
            'throttle'  => $throttle,
            'issues'    => $issues,
        ];
    }

    /**
     * @param array<string, class-string>       $middlewareAliases
     * @param array<string, array<int, string>> $middlewareGroups
     */
    private function middlewareExists(string $alias, array $middlewareAliases, array $middlewareGroups, array $resolved): bool
    {
        if (isset($middlewareAliases[$alias])) {
            return true;
        }

        if (isset($middlewareGroups[$alias])) {
            return true;
        }

        foreach ($resolved as $middleware) {
            if (str_contains($middleware, $alias)) {
                return true;
            }
        }

        if (class_exists($alias)) {
            return true;
        }

        return false;
    }

    private function extractThrottleLimiterName(string $parameterPayload): ?string
    {
        $segments = array_map('trim', explode(',', $parameterPayload));
        if ($segments === []) {
            return null;
        }

        $candidate = $segments[0];
        if ($candidate === '' || is_numeric($candidate)) {
            return null;
        }

        return $candidate;
    }

    /**
     * @param  list<array<string, mixed>> $entries
     * @return list<array<string, mixed>>
     */
    private function applyDuplicateNameChecks(array $entries): array
    {
        $names = [];

        foreach ($entries as $entry) {
            $name = (string) ($entry['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $names[$name][] = $entry;
        }

        foreach ($names as $name => $group) {
            if (count($group) <= 1) {
                continue;
            }

            foreach ($entries as &$entry) {
                if ((string) $entry['name'] === (string) $name) {
                    $entry['staticIssues'][] = Issue::error('Duplicate route name detected.', [
                        'name' => $name,
                    ]);
                }
            }
        }

        $normalized = [];
        foreach (array_keys($names) as $name) {
            $normalizedKey = Str::slug($name);
            $normalized[$normalizedKey][] = $name;
        }

        foreach ($normalized as $key => $originalNames) {
            $uniqueOriginals = array_values(array_unique($originalNames));
            if (count($uniqueOriginals) <= 1) {
                continue;
            }

            foreach ($entries as &$entry) {
                if (in_array((string) $entry['name'], $uniqueOriginals, true)) {
                    $entry['staticIssues'][] = Issue::warning('Route name is very similar to another route and may cause confusion.', [
                        'related' => $uniqueOriginals,
                    ]);
                }
            }
        }

        return $entries;
    }

    /**
     * Inspect route parameters and attempt to correlate them with controller signature hints.
     *
     * @param array<class-string, class-string> $policies
     * @return array{
     *   issues: list<array<string, mixed>>,
     *   controllerParameters: array<string, array<string, mixed>>,
     *   routeParameters: list<array<string, mixed>>,
     *   constraints: array<string, mixed>,
     *   policyHints: array<string, mixed>
     * }
     */
    private function inspectParameters(
        Route $route,
        ?ReflectionMethod $method,
        array $issues,
        array $policies
    ): array {
        $parameterMeta = [];
        $controllerParameters = [];
        $policyHints = [];
        $constraints = $route->wheres;

        foreach ($constraints as $parameter => $expression) {
            if (@preg_match('/' . $expression . '/', '') === false) {
                $issues[] = Issue::warning('Route parameter constraint has invalid regular expression.', [
                    'parameter'  => $parameter,
                    'constraint' => $expression,
                ]);
            }
        }

        $parameterNames = $route->parameterNames();

        foreach ($parameterNames as $parameterName) {
            $isOptional = Str::contains($route->uri(), '{' . $parameterName . '?}');
            $parameterMeta[] = [
                'name'        => $parameterName,
                'required'    => ! $isOptional,
                'constraint'  => $constraints[$parameterName] ?? null,
                'bindingType' => null,
            ];
        }

        if (! $method instanceof ReflectionMethod) {
            return [
                'issues'               => $issues,
                'controllerParameters' => $controllerParameters,
                'routeParameters'      => $parameterMeta,
                'constraints'          => $constraints,
                'policyHints'          => $policyHints,
            ];
        }

        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();

            $controllerParameters[$parameterName] = [
                'type'       => null,
                'allowsNull' => $parameter->allowsNull(),
                'isModel'    => false,
                'binding'    => null,
            ];

            $type = $parameter->getType();
            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $typeName = $type->getName();
            $controllerParameters[$parameterName]['type'] = $typeName;

            if (is_subclass_of($typeName, Model::class)) {
                $controllerParameters[$parameterName]['isModel'] = true;

                if (! class_exists($typeName)) {
                    $issues[] = Issue::error('Route-model binding references missing model class.', [
                        'parameter' => $parameterName,
                        'model'     => $typeName,
                    ]);
                }

                if (! $type->allowsNull() && $parameter->isOptional()) {
                    $issues[] = Issue::warning('Route parameter default conflicts with non-nullable model binding.', [
                        'parameter' => $parameterName,
                        'model'     => $typeName,
                    ]);
                }

                foreach ($parameterMeta as &$meta) {
                    if ($meta['name'] === $parameterName) {
                        $meta['bindingType'] = $typeName;
                    }
                }
            }

            if (isset($policies[$typeName])) {
                $policyHints[$parameterName] = [
                    'model'  => $typeName,
                    'policy' => $policies[$typeName],
                ];
            }
        }

        return [
            'issues'               => $issues,
            'controllerParameters' => $controllerParameters,
            'routeParameters'      => $parameterMeta,
            'constraints'          => $constraints,
            'policyHints'          => $policyHints,
        ];
    }
}
