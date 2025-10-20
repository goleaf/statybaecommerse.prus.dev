<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\Domain\DomainException;
use App\Services\TranslationService;
use App\Support\ErrorCodes;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiErrorResponse
{
    private const TYPE_NAMESPACE = 'tag:statybaecommerse.prus.dev,2024:error:';

    private function __construct() {}

    public static function fromThrowable(Throwable $throwable, Request $request): JsonResponse
    {
        if ($throwable instanceof DomainException) {
            return self::fromDomainException($throwable, $request);
        }

        if ($throwable instanceof ValidationException) {
            return self::problem(
                request: $request,
                errorCode: ErrorCodes::VALIDATION_FAILED,
                status: $throwable->status,
                title: __('Validation failed'),
                detail: __('The submitted data was invalid.'),
                extensions: [
                    'error' => [
                        'context' => $throwable->errors(),
                    ],
                ],
            );
        }

        if ($throwable instanceof AuthenticationException) {
            return self::problem(
                request: $request,
                errorCode: ErrorCodes::UNAUTHORIZED,
                status: HttpFoundationResponse::HTTP_UNAUTHORIZED,
                title: __('Unauthorized'),
                detail: __('Authentication is required to access this resource.'),
            );
        }

        if ($throwable instanceof AuthorizationException) {
            return self::problem(
                request: $request,
                errorCode: ErrorCodes::FORBIDDEN,
                status: HttpFoundationResponse::HTTP_FORBIDDEN,
                title: __('Forbidden'),
                detail: __('You do not have permission to perform this action.'),
            );
        }

        if ($throwable instanceof NotFoundHttpException) {
            return self::problem(
                request: $request,
                errorCode: ErrorCodes::NOT_FOUND,
                status: HttpFoundationResponse::HTTP_NOT_FOUND,
                title: __('Not Found'),
                detail: __('The requested resource could not be located.'),
            );
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $status = $throwable->getStatusCode();
            $title = HttpFoundationResponse::$statusTexts[$status] ?? __('HTTP Error');
            $detail = trim((string) $throwable->getMessage()) !== ''
                ? $throwable->getMessage()
                : __('An HTTP error occurred while processing the request.');

            return self::problem(
                request: $request,
                errorCode: ErrorCodes::SERVER_ERROR,
                status: $status,
                title: $title,
                detail: $detail,
            );
        }

        return self::problem(
            request: $request,
            errorCode: ErrorCodes::SERVER_ERROR,
            status: HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR,
            title: __('Internal Server Error'),
            detail: __('An unexpected error occurred. Please try again later.'),
        );
    }

    private static function fromDomainException(DomainException $exception, Request $request): JsonResponse
    {
        $locale = self::determineLocale($request);

        App::setLocale($locale);
        App::instance('request_locale', $locale);

        $message = TranslationService::get($exception->translationKey(), $exception->context(), $locale);

        Log::withContext([
            'correlation_id' => self::correlationId($request),
            'locale' => $locale,
            'error_code' => $exception->errorCode(),
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);

        Log::warning('Domain exception rendered.', [
            'exception' => $exception::class,
            'status' => $exception->status(),
            'translation_key' => $exception->translationKey(),
            'context' => $exception->context(),
        ]);

        return self::problem(
            request: $request,
            errorCode: $exception->errorCode(),
            status: $exception->status(),
            title: $message,
            detail: $message,
            locale: $locale,
            extensions: [
                'error' => [
                    'context' => $exception->context(),
                ],
            ],
        );
    }

    private static function problem(
        Request $request,
        string $errorCode,
        int $status,
        string $title,
        string $detail,
        ?string $locale = null,
        array $extensions = []
    ): JsonResponse {
        $correlationId = self::correlationId($request);
        $instance = $request->fullUrl() !== '' ? $request->fullUrl() : '/'.ltrim($request->path(), '/');

        $payload = [
            'type' => self::typeFor($errorCode),
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance,
            'correlation_id' => $correlationId,
            'error' => [
                'code' => $errorCode,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        if ($locale !== null) {
            $payload['error']['locale'] = $locale;
        }

        $payload = array_replace_recursive($payload, $extensions);

        $response = response()->json($payload, $status);

        $headerName = config('app.correlation_header', 'X-Correlation-ID');
        $response->headers->set($headerName, $correlationId);

        if ($locale !== null) {
            $response->headers->set('Content-Language', $locale);
        }

        return $response;
    }

    private static function correlationId(Request $request): string
    {
        $attributeCorrelationRaw = $request->attributes->get('correlation_id');
        $attributeCorrelationId = is_string($attributeCorrelationRaw) ? $attributeCorrelationRaw : null;

        if ($attributeCorrelationId !== null && $attributeCorrelationId !== '') {
            return $attributeCorrelationId;
        }

        if (App::bound('request_correlation_id')) {
            $resolved = App::make('request_correlation_id');
            if (is_string($resolved) && $resolved !== '') {
                return $resolved;
            }
        }

        $generated = Str::uuid()->toString();
        $request->attributes->set('correlation_id', $generated);
        App::instance('request_correlation_id', $generated);

        return $generated;
    }

    private static function determineLocale(Request $request): string
    {
        $availableLocales = TranslationService::getAvailableLocales();
        $preferred = $request->getPreferredLanguage($availableLocales);
        $currentLocale = App::getLocale();
        $locale = is_string($preferred) && $preferred !== ''
            ? $preferred
            : ($currentLocale !== ''
                ? $currentLocale
                : TranslationService::getDefaultLocale());

        if (! in_array($locale, $availableLocales, true)) {
            $locale = TranslationService::getDefaultLocale();
        }

        return $locale;
    }

    private static function typeFor(string $errorCode): string
    {
        return self::TYPE_NAMESPACE.$errorCode;
    }
}
