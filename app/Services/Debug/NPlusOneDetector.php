<?php

declare(strict_types=1);

namespace App\Services\Debug;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class NPlusOneDetector
{
    private const THRESHOLD = 3;

    /**
     * @var array<string, array{count:int, sql:string, location:string|null}>
     */
    private array $fingerprints = [];

    /**
     * Register the query listener that detects repeated select statements.
     */
    public function register(): void
    {
        Event::listen(QueryExecuted::class, function (QueryExecuted $event): void {
            if (! $this->shouldInspect($event->sql)) {
                return;
            }

            $fingerprint = $this->fingerprint($event->sql, $event->bindings);
            $current = $this->fingerprints[$fingerprint]['count'] ?? 0;
            $nextCount = $current + 1;

            $this->fingerprints[$fingerprint] = [
                'count'    => $nextCount,
                'sql'      => $event->sql,
                'location' => $this->resolveCaller(),
            ];

            if ($nextCount === self::THRESHOLD) {
                $this->report($fingerprint, $this->fingerprints[$fingerprint]);
            }
        });
    }

    private function shouldInspect(string $sql): bool
    {
        return str_starts_with(Str::lower(ltrim($sql)), 'select');
    }

    /**
     * @param array<int, mixed> $bindings
     */
    private function fingerprint(string $sql, array $bindings): string
    {
        return md5($sql . '|' . serialize(Arr::map($bindings, static fn ($binding) => is_scalar($binding) ? $binding : gettype($binding))));
    }

    /**
     * @param array{count:int, sql:string, location:string|null} $payload
     */
    private function report(string $fingerprint, array $payload): void
    {
        $message = sprintf(
            'Detected potential N+1 query (executed %d times): %s',
            $payload['count'],
            Str::limit(preg_replace('/\s+/', ' ', $payload['sql']), 200)
        );

        $context = [
            'fingerprint' => $fingerprint,
            'occurrences' => $payload['count'],
            'origin'      => $payload['location'],
        ];

        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage(array_merge(['message' => $message], $context), 'n+1');
            } catch (Throwable $e) {
                // Ignore debugbar transport errors while still logging to disk.
            }
        }

        Log::warning($message, $context);
    }

    private function resolveCaller(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);

        foreach ($trace as $frame) {
            $file = $frame['file'] ?? null;
            if ($file !== null && str_contains($file, DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR)) {
                $line = $frame['line'] ?? null;

                return $line === null ? $file : sprintf('%s:%s', $file, $line);
            }
        }

        return null;
    }
}
