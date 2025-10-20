<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tracing\Trace;
use App\Support\Tracing\TraceContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class AttachCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = config('app.correlation_header', 'X-Correlation-ID');
        $incoming = (string) $request->headers->get($headerName, '');
        $correlationId = $incoming !== '' ? $incoming : Str::uuid()->toString();

        $request->attributes->set('correlation_id', $correlationId);
        App::instance('request_correlation_id', $correlationId);

        $traceContext = TraceContext::fromHeaders($request->headers, $correlationId);
        Trace::store($traceContext);
        $request->attributes->set('trace_context', $traceContext);

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set($headerName, $correlationId);
        $response->headers->set('traceparent', $traceContext->toTraceParent());
        $response->headers->set('X-Trace-Id', $traceContext->traceId());
        $response->headers->set('X-Span-Id', $traceContext->spanId());
        if ($traceContext->parentSpanId() !== null) {
            $response->headers->set('X-Parent-Span-Id', $traceContext->parentSpanId());
        }

        return $response;
    }
}
