<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Logging\LogContext;
use App\Support\Logging\StructuredLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AssignCorrelationId
{
    public function __construct(
        private readonly LogContext $logContext,
        private readonly StructuredLogger $logger,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->headers->get('X-Correlation-ID') ?: (string) Str::uuid();
        $requestId = (string) Str::uuid();

        $this->logContext->setCorrelationId($correlationId);
        $this->logContext->setRequestId($requestId);
        $this->logContext->merge([
            'http_method' => $request->getMethod(),
            'path'        => '/' . ltrim($request->path(), '/'),
            'ip'          => $request->ip(),
        ]);

        Log::withContext($this->logContext->toArray());

        $operation = $this->logger->operation('http_request', [
            'method' => $request->getMethod(),
            'path'   => '/' . ltrim($request->path(), '/'),
        ]);

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (Throwable $throwable) {
            $this->logContext->setUserId($request->user()?->getAuthIdentifier());
            Log::withContext($this->logContext->toArray());

            $operation->fail($throwable, [
                'status_code' => method_exists($throwable, 'getStatusCode') ? (int) $throwable->getStatusCode() : 500,
            ]);

            throw $throwable;
        }

        $this->logContext->setUserId($request->user()?->getAuthIdentifier());
        $this->logContext->merge([
            'status_code' => $response->getStatusCode(),
        ]);
        Log::withContext($this->logContext->toArray());

        $operation->finish([
            'status_code'    => $response->getStatusCode(),
            'content_length' => (int) $response->headers->get('Content-Length', 0),
        ]);

        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
