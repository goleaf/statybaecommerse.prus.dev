<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Helper methods that convert Laravel JSON responses into PSR-7 responses and validate
 * them against the published OpenAPI contract for public collection endpoints. Centralising
 * this logic keeps the tests expressive while ensuring we fail fast when the transport layer changes.
 */
trait AssertsAgainstOpenApi
{
    /**
     * Cached response validator instance so we only parse the OpenAPI document once per test run.
     */
    private static ?ResponseValidator $collectionResponseValidator = null;

    /**
     * Cached PSR-17/PSR-18 bridge factory so that response conversions remain cheap.
     */
    private static ?PsrHttpFactory $psrHttpFactory = null;

    /**
     * Validate the given response against the OpenAPI schema using the provided operation metadata.
     *
     * @param TestResponse $response The Laravel JSON response returned by the application under test.
     * @param string       $path     The OpenAPI path template (e.g. `/collections/api/search`).
     * @param string       $method   The HTTP method in lower- or upper-case form (defaults to GET).
     */
    protected function assertResponseMatchesCollectionOpenApi(TestResponse $response, string $path, string $method = 'get'): void
    {
        $psrResponse = $this->convertToPsrResponse($response);

        try {
            $this->getCollectionResponseValidator()->validate(
                $psrResponse,
                new OperationAddress($path, strtolower($method))
            );
            $this->addToAssertionCount(1);
        } catch (ValidationFailed $exception) {
            $this->fail(sprintf(
                'OpenAPI validation failed for [%s %s]: %s',
                strtoupper($method),
                $path,
                $exception->getMessage()
            ));
        }
    }

    /**
     * Convert a Laravel test response to a PSR-7 implementation that the validator understands.
     */
    private function convertToPsrResponse(TestResponse $response): ResponseInterface
    {
        return $this->getPsrHttpFactory()->createResponse($response->baseResponse);
    }

    /**
     * Lazily load the response validator for the collection contract.
     */
    private function getCollectionResponseValidator(): ResponseValidator
    {
        if (self::$collectionResponseValidator instanceof ResponseValidator) {
            return self::$collectionResponseValidator;
        }

        $builder = (new ValidatorBuilder)
            ->fromYamlFile($this->collectionOpenApiSpecPath())
            ->setCache(new ArrayAdapter);

        self::$collectionResponseValidator = $builder->getResponseValidator();

        return self::$collectionResponseValidator;
    }

    /**
     * Provide the absolute path to the OpenAPI document describing the storefront collection endpoints.
     */
    private function collectionOpenApiSpecPath(): string
    {
        return base_path('docs/openapi/collections.public.yaml');
    }

    /**
     * Lazily create the PSR HTTP factory used to transform Symfony responses into PSR-7 responses.
     */
    private function getPsrHttpFactory(): PsrHttpFactory
    {
        if (self::$psrHttpFactory instanceof PsrHttpFactory) {
            return self::$psrHttpFactory;
        }

        $psr17Factory = new Psr17Factory;
        self::$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        return self::$psrHttpFactory;
    }
}
