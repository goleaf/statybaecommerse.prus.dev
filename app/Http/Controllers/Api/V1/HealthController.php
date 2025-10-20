<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Queue\Factory as QueueFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController extends Controller
{
    public function __construct(private readonly QueueFactory $queueFactory)
    {
    }

    public function health(): JsonResponse
    {
        return $this->buildResponse();
    }

    public function ready(): JsonResponse
    {
        return $this->buildResponse();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function runChecks(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $checks
     */
    private function determineStatusCode(array $checks): int
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? 'failed') !== 'ok' && empty($check['optional'])) {
                return 503;
            }
        }

        return 200;
    }

    private function buildResponse(): JsonResponse
    {
        $checks = $this->runChecks();
        $statusCode = $this->determineStatusCode($checks);

        return response()
            ->json([
                'status' => $statusCode === 200 ? 'ok' : 'error',
                'timestamp' => now()->toIso8601String(),
                'version' => [
                    'hash' => $this->getAppVersionHash(),
                ],
                'checks' => $checks,
            ], $statusCode)
            ->header('Cache-Control', 'no-store');
    }

    /**
     * @return array<string, mixed>
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $exception) {
            return [
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function checkCache(): array
    {
        try {
            $key = 'health-check:'.uniqid('', true);

            Cache::store()->set($key, true, 5);
            Cache::store()->forget($key);

            return ['status' => 'ok'];
        } catch (Throwable $exception) {
            return [
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function checkQueue(): array
    {
        try {
            $connection = $this->queueFactory->connection();
            $connection->getConnectionName();

            return [
                'status' => 'ok',
                'optional' => true,
            ];
        } catch (Throwable $exception) {
            return [
                'status' => 'failed',
                'optional' => true,
                'message' => $exception->getMessage(),
            ];
        }
    }

    private function getAppVersionHash(): string
    {
        return (string) (config('app.version_hash')
            ?? config('app.commit_hash')
            ?? env('APP_VERSION_HASH')
            ?? 'unknown');
    }
}
