<?php

declare(strict_types=1);

namespace App\Support;

use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

final class ApiErrorResponse
{
    public static function fromThrowable(Throwable $exception, Request $request): JsonResponse
    {
        $correlationId = (string) Str::uuid();
        $status = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
        $code = 'server_error';
        $message = __('An unexpected error occurred.');
        $details = [];

        if ($exception instanceof ValidationException) {
            $status = $exception->status;
            $code = 'validation_error';
            $message = $exception->getMessage() ?: __('The given data was invalid.');
            $details = ['errors' => $exception->errors()];
        } elseif ($exception instanceof AuthenticationException) {
            $status = SymfonyResponse::HTTP_UNAUTHORIZED;
            $code = 'unauthenticated';
            $message = $exception->getMessage() ?: __('Unauthenticated.');
        } elseif ($exception instanceof AuthorizationException) {
            $status = SymfonyResponse::HTTP_FORBIDDEN;
            $code = 'forbidden';
            $message = $exception->getMessage() ?: __('This action is unauthorized.');
        } elseif ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            $status = SymfonyResponse::HTTP_NOT_FOUND;
            $code = 'resource_not_found';
            $message = __('Resource not found.');
        } elseif ($exception instanceof TooManyRequestsHttpException) {
            $status = SymfonyResponse::HTTP_TOO_MANY_REQUESTS;
            $code = 'rate_limited';
            $message = $exception->getMessage() ?: __('Too many requests.');
            $retryAfter = $exception->getHeaders()['Retry-After'] ?? null;
            if ($retryAfter !== null) {
                $details['retry_after'] = is_numeric($retryAfter) ? (int) $retryAfter : $retryAfter;
            }
        } elseif ($exception instanceof DomainException) {
            $status = SymfonyResponse::HTTP_BAD_REQUEST;
            $code = 'domain_error';
            $message = $exception->getMessage() ?: __('A domain error occurred.');
        } elseif ($exception instanceof HttpExceptionInterface) {
            $status = $exception->getStatusCode();
            $code = 'http_error';
            $message = $exception->getMessage() ?: (SymfonyResponse::$statusTexts[$status] ?? __('HTTP error.'));
        }

        $context = [
            'correlation_id' => $correlationId,
            'exception' => $exception,
            'request_url' => $request->fullUrl(),
            'request_method' => $request->method(),
        ];

        if ($status >= SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR) {
            Log::error($message, $context);
        } else {
            Log::warning($message, $context);
        }

        $payload = [
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => empty($details) ? null : $details,
                'correlation_id' => $correlationId,
            ],
        ];

        return response()
            ->json($payload, $status)
            ->header('Content-Type', 'application/problem+json');
    }
}
