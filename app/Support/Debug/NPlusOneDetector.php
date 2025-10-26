<?php

declare(strict_types=1);

namespace App\Support\Debug;

use DateTimeInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class NPlusOneDetector
{
    private const MIN_DUPLICATE_COUNT = 5;

    /**
     * @var array<string, array{count:int,sql:string,binding_sample:array<int,mixed>,location:string}>
     */
    private array $queries = [];

    private bool $enabled = false;

    public function boot(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $this->enabled = true;

        DB::listen(function (QueryExecuted $event): void {
            $this->recordQuery($event);
        });

        app()->terminating(function (): void {
            $this->reportDuplicates();
        });
    }

    private function recordQuery(QueryExecuted $event): void
    {
        if (! $this->enabled) {
            return;
        }

        $location = $this->findApplicationFrame();
        $fingerprint = md5($event->sql . '|' . serialize($this->normalizeBindings($event->bindings)) . "|{$location}");

        if (! isset($this->queries[$fingerprint])) {
            $this->queries[$fingerprint] = [
                'count'          => 0,
                'sql'            => $event->sql,
                'binding_sample' => Arr::map($event->bindings, function ($binding) {
                    if ($binding instanceof DateTimeInterface) {
                        return $binding->format('c');
                    }

                    return $binding;
                }),
                'location' => $location,
            ];
        }

        $this->queries[$fingerprint]['count']++;
    }

    private function reportDuplicates(): void
    {
        if (! $this->enabled || empty($this->queries)) {
            return;
        }

        $offenders = array_filter($this->queries, fn (array $data): bool => $data['count'] >= self::MIN_DUPLICATE_COUNT);

        if (empty($offenders)) {
            return;
        }

        usort($offenders, fn (array $a, array $b): int => $b['count'] <=> $a['count']);
        $topOffenders = array_slice($offenders, 0, 5);

        $payload = array_map(function (array $data) {
            return [
                'count'    => $data['count'],
                'sql'      => Str::limit($data['sql'], 250),
                'location' => $data['location'],
                'bindings' => $data['binding_sample'],
            ];
        }, $topOffenders);

        if (function_exists('debugbar') && app()->bound('debugbar')) {
            try {
                app('debugbar')->addMessage(['n_plus_one' => $payload], 'queries');
            } catch (Throwable $exception) {
                Log::debug('Failed pushing N+1 details to debugbar', ['error' => $exception->getMessage()]);
            }
        }

        Log::warning('Detected potential N+1 queries', ['offenders' => $payload]);
    }

    private function findApplicationFrame(): string
    {
        $basePath = base_path() . DIRECTORY_SEPARATOR;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);

        foreach ($trace as $frame) {
            if (! isset($frame['file'], $frame['line'])) {
                continue;
            }

            $file = $frame['file'];

            if (str_starts_with($file, $basePath . 'vendor')) {
                continue;
            }

            if (str_starts_with($file, $basePath . 'storage')) {
                continue;
            }

            if (str_starts_with($file, $basePath . 'bootstrap')) {
                continue;
            }

            return str_replace($basePath, '', $file) . ':' . $frame['line'];
        }

        return 'unknown';
    }

    /**
     * @param  array<int, mixed> $bindings
     * @return array<int, mixed>
     */
    private function normalizeBindings(array $bindings): array
    {
        return array_map(function ($binding) {
            if ($binding instanceof DateTimeInterface) {
                return $binding->getTimestamp();
            }

            if (is_object($binding)) {
                return spl_object_hash($binding);
            }

            return $binding;
        }, $bindings);
    }
}
