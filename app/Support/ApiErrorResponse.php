<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiErrorResponse
{
    private const DEFAULT_PROBLEM_BASE_URI = 'https://prus.dev/problems';

    private function __construct()
    {
    }

    /**
     * Build an RFC 7807 problem details response enriched with correlation metadata.
     *
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $extra
     */
    public static function problem(
        Request $request,
        string $errorCode,
        string $detail,
        int $status,
        ?string $title = null,
        array $context = [],
        ?string $locale = null,
        array $extra = []
    ): JsonResponse {
        ErrorCodes::assertValid($errorCode);

        $locale ??= RequestContext::resolveLocale($request);
        $traceId = RequestContext::resolveTraceId($request);
        $problem = [
            'type' => self::typeFor($errorCode),
            'title' => $title ?? self::titleFor($errorCode, $locale),
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->fullUrl(),
            'error' => array_filter([
                'code' => $errorCode,
                'context' => $context,
            ], static fn (mixed $value) => $value !== null && $value !== []),
            'correlation' => [
                'trace_id' => $traceId,
                'correlation_id' => $traceId,
            ],
            'meta' => [
                'locale' => $locale,
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if ($extra !== []) {
            $problem = array_replace_recursive($problem, $extra);
        }

        $response = response()->json($problem, $status);

        $response->headers->set(RequestContext::correlationHeader(), $traceId);
        $response->headers->set('Content-Language', $locale);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

    public static function typeFor(string $errorCode): string
    {
        $base = (string) config('app.problem_base_uri', self::DEFAULT_PROBLEM_BASE_URI);

        return rtrim($base, '/').'/'.$errorCode;
    }

    public static function titleFor(string $errorCode, ?string $locale = null): string
    {
        // Prefer localized labels from the translation files and gracefully
        // fall back to the built-in descriptions if a translation is missing.
        return ErrorCodes::title($errorCode, $locale)
            ?? ErrorCodes::describe($errorCode)
            ?? 'Application error';
    }
}
