<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\HealthReporter as HealthReporterContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class HealthController extends Controller
{
    public function __construct(private readonly HealthReporterContract $reporter) {}

    public function health(): JsonResponse
    {
        return $this->respond($this->reporter->report());
    }

    public function ready(): JsonResponse
    {
        return $this->respond($this->reporter->report(includeQueue: true));
    }

    /**
     * @param array{status: string, checks: array<string, array<string, mixed>>, timestamp: string} $payload
     */
    private function respond(array $payload): JsonResponse
    {
        $statusCode = $payload['status'] === 'ok' ? 200 : 503;
        $locale = app()->getLocale();

        return response()
            ->json($payload, $statusCode)
            ->header('Cache-Control', 'no-store, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Content-Language', $locale);
    }
}
