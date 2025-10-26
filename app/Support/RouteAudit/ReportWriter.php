<?php

declare(strict_types=1);

namespace App\Support\RouteAudit;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;

final class ReportWriter
{
    private Filesystem $files;

    public function __construct(?Filesystem $files = null)
    {
        $this->files = $files ?? App::make(Filesystem::class);
    }

    /**
     * @param array<string, mixed> $staticReport
     * @param array<string, mixed> $dynamicReport
     */
    public function write(array $staticReport, array $dynamicReport, string $jsonPath, string $markdownPath): void
    {
        $merged = $this->merge($staticReport, $dynamicReport);

        $this->writeJson($merged, $jsonPath);
        $this->writeMarkdown($merged, $markdownPath);
    }

    /**
     * @param  array<string, mixed> $staticReport
     * @param  array<string, mixed> $dynamicReport
     * @return array<string, mixed>
     */
    private function merge(array $staticReport, array $dynamicReport): array
    {
        $dynamicRoutes = [];

        foreach ($dynamicReport['routes'] ?? [] as $entry) {
            if (! isset($entry['fingerprint'])) {
                continue;
            }
            $dynamicRoutes[$entry['fingerprint']] = $entry;
        }

        $routes = [];

        foreach ($staticReport['routes'] ?? [] as $route) {
            $fingerprint = (string) $route['fingerprint'];

            $dynamic = $dynamicRoutes[$fingerprint] ?? [
                'guest'  => null,
                'auth'   => null,
                'notes'  => '',
                'status' => 'not-tested',
            ];

            $route['dynamic'] = $dynamic;
            $routes[] = $route;
        }

        $errorCount = (int) ($staticReport['errors'] ?? 0);
        $warningCount = (int) ($staticReport['warnings'] ?? 0);

        foreach ($dynamicRoutes as $fingerprint => $entry) {
            if (($entry['status'] ?? '') === 'failed') {
                $errorCount++;
            }
        }

        return [
            'generatedAt' => now()->toIso8601String(),
            'routes'      => $routes,
            'static'      => $staticReport,
            'dynamic'     => $dynamicReport,
            'summary'     => [
                'totalRoutes'     => count($routes),
                'staticErrors'    => (int) ($staticReport['errors'] ?? 0),
                'staticWarnings'  => (int) ($staticReport['warnings'] ?? 0),
                'dynamicFailures' => count(array_filter($routes, static function ($route): bool {
                    return ($route['dynamic']['status'] ?? '') === 'failed';
                })),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $report
     */
    private function writeJson(array $report, string $path): void
    {
        $this->ensureDirectory(dirname($path));

        $encoded = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $this->files->put($path, $encoded === false ? '{}' : $encoded);
    }

    /**
     * @param array<string, mixed> $report
     */
    private function writeMarkdown(array $report, string $path): void
    {
        $this->ensureDirectory(dirname($path));

        $lines = [];
        $lines[] = '# Route Audit';
        $lines[] = '';

        $summary = $report['summary'] ?? [];

        $lines[] = sprintf(
            '*Generated at %s – %d routes inspected (%d static errors, %d warnings, %d dynamic failures).*',
            $report['generatedAt'] ?? now()->toIso8601String(),
            $summary['totalRoutes'] ?? count($report['routes'] ?? []),
            $summary['staticErrors'] ?? 0,
            $summary['staticWarnings'] ?? 0,
            $summary['dynamicFailures'] ?? 0
        );

        $lines[] = '';
        $lines[] = '| Methods | URI | Name | Action | Middleware | Auth | Params | Static Issues | Dynamic | Notes |';
        $lines[] = '| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |';

        foreach ($report['routes'] ?? [] as $route) {
            $lines[] = $this->markdownRow($route);
        }

        $this->files->put($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    /**
     * @param array<string, mixed> $route
     */
    private function markdownRow(array $route): string
    {
        $methods = implode(',', $route['methods'] ?? []);
        $uri = $route['uri'] ?? '/';
        $name = $route['name'] ?? '';
        $action = $route['action'] ?? '';
        $middleware = implode(',', $route['middlewares']['declared'] ?? []);
        $auth = $route['middlewares']['auth'] ?? 'guest';

        $params = [];
        foreach ($route['parameters'] ?? [] as $parameter) {
            $segment = $parameter['name'];
            if (! empty($parameter['bindingType'])) {
                $segment .= ':' . class_basename((string) $parameter['bindingType']);
            }
            $params[] = $segment;
        }

        $staticIssues = $this->formatIssues($route['staticIssues'] ?? []);
        $dynamic = $this->formatDynamic($route['dynamic'] ?? []);
        $notes = trim((string) ($route['notes'] ?? ''));

        return sprintf(
            '| %s | `%s` | %s | %s | %s | %s | %s | %s | %s | %s |',
            $methods,
            $uri,
            $name !== '' ? '`' . $name . '`' : '—',
            $action !== '' ? '`' . $action . '`' : '—',
            $middleware !== '' ? '`' . $middleware . '`' : '—',
            $auth !== null ? '`' . $auth . '`' : '—',
            $params !== [] ? '`' . implode('`, `', $params) . '`' : '—',
            $staticIssues !== '' ? $staticIssues : '—',
            $dynamic !== '' ? $dynamic : '—',
            $notes !== '' ? $notes : '—'
        );
    }

    /**
     * @param list<array<string, mixed>> $issues
     */
    private function formatIssues(array $issues): string
    {
        if ($issues === []) {
            return '';
        }

        $segments = [];
        foreach ($issues as $issue) {
            $severity = strtoupper((string) ($issue['severity'] ?? 'info'));
            $message = (string) ($issue['message'] ?? '');
            $segments[] = sprintf('**%s** %s', $severity, $message);
        }

        return implode('<br>', $segments);
    }

    /**
     * @param array<string, mixed> $dynamic
     */
    private function formatDynamic(array $dynamic): string
    {
        if ($dynamic === []) {
            return '';
        }

        $status = strtoupper((string) ($dynamic['status'] ?? 'unknown'));

        $summary = [];
        foreach (['guest', 'auth'] as $context) {
            $contextData = $dynamic[$context] ?? null;
            if (! is_array($contextData)) {
                continue;
            }

            $contextStatus = $contextData['status'] ?? null;
            if ($contextStatus === null) {
                continue;
            }

            $summary[] = sprintf('%s:%s', $context, $contextStatus);
        }

        if ($summary !== []) {
            $status .= ' (' . implode(', ', $summary) . ')';
        }

        if (! empty($dynamic['error'])) {
            $status .= ' — ' . $dynamic['error'];
        }

        return $status;
    }

    private function ensureDirectory(string $directory): void
    {
        if ($directory === '' || $directory === '.' || $this->files->exists($directory)) {
            return;
        }

        $this->files->ensureDirectoryExists($directory);
    }
}
