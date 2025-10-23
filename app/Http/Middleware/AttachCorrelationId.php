<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set($headerName, $correlationId);

        return $response;
    }
}
