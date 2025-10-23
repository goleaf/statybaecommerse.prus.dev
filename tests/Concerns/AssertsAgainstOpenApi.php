<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Exception;
use Illuminate\Testing\TestResponse;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function base_path;
use function file_exists;
use function sprintf;
use function strtoupper;

trait AssertsAgainstOpenApi
{
    private static ?ResponseValidator $openApiResponseValidator = null;

    /**
     * @param  TestResponse<\Symfony\Component\HttpFoundation\Response>  $response
     */
    protected function assertResponseMatchesOpenApi(TestResponse $response, string $path, string $method = 'GET'): void
    {
        $validator = self::getResponseValidator();
        $operation = new OperationAddress($path, strtoupper($method));
        $psrResponse = $this->toPsrResponse($response);

        try {
            $validator->validate($operation, $psrResponse);
        } catch (ValidationFailed $exception) {
            throw new ExpectationFailedException(
                sprintf('Failed asserting response matches OpenAPI schema for [%s %s]: %s', strtoupper($method), $path, $exception->getMessage()),
                null,
                $exception
            );
        } catch (Throwable $exception) {
            $previous = $exception instanceof Exception
                ? $exception
                : new Exception($exception->getMessage(), 0, $exception);

            throw new ExpectationFailedException(
                sprintf('OpenAPI validation failed for [%s %s]: %s', strtoupper($method), $path, $exception->getMessage()),
                null,
                $previous
            );
        }
    }

    private static function getResponseValidator(): ResponseValidator
    {
        if (self::$openApiResponseValidator instanceof ResponseValidator) {
            return self::$openApiResponseValidator;
        }

        $schemaPath = base_path('public/openapi.json');

        if (! file_exists($schemaPath)) {
            throw new ExpectationFailedException(sprintf('OpenAPI schema file was not found at %s', $schemaPath));
        }

        self::$openApiResponseValidator = (new ValidatorBuilder)
            ->fromJsonFile($schemaPath)
            ->getResponseValidator();

        return self::$openApiResponseValidator;
    }

    /**
     * @param  TestResponse<\Symfony\Component\HttpFoundation\Response>  $response
     */
    private function toPsrResponse(TestResponse $response): ResponseInterface
    {
        $factory = new Psr17Factory;
        $psrResponse = $factory->createResponse($response->getStatusCode());

        foreach ($response->headers->all() as $header => $values) {
            foreach ($values as $value) {
                if ($value === null) {
                    continue;
                }

                $psrResponse = $psrResponse->withAddedHeader($header, $value);
            }
        }

        if (! $psrResponse->hasHeader('Content-Type')) {
            $contentType = (string) $response->headers->get('Content-Type', 'application/json');
            $psrResponse = $psrResponse->withHeader('Content-Type', $contentType);
        }

        $content = $response->getContent();
        $psrResponse = $psrResponse->withBody($factory->createStream($content === false ? '' : $content));

        return $psrResponse;
    }
}
