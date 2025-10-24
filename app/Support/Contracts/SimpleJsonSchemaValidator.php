<?php

declare(strict_types=1);

namespace App\Support\Contracts;

use App\Support\Contracts\Entities\BrandContract;
use App\Support\Contracts\Entities\CategoryContract;
use App\Support\Contracts\Entities\OrderContract;
use App\Support\Contracts\Entities\ProductContract;
use App\Support\Contracts\Entities\UserContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class SimpleJsonSchemaValidator
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $schemaCache = [];

    public function __construct(private readonly Filesystem $filesystem) {}

    /**
     * Validate the given payload.
     *
     * Usage scenarios:
     * - validate($payload, $schemaPath) to validate against a full schema file.
     * - validate($contract, $payload) to validate an entity payload against the schema's $defs entry.
     *
     * @param  array<string, mixed>|list<mixed>|string  $payloadOrContract
     * @param  array<string, mixed>|list<mixed>|string|null  $schemaOrPayload
     * @return array<int, string> Validation error messages. Empty when the payload is valid.
     */
    public function validate(mixed $payloadOrContract, mixed $schemaOrPayload = null): array
    {
        if (is_string($payloadOrContract) && is_array($schemaOrPayload)) {
            return $this->validateContractPayload($payloadOrContract, $schemaOrPayload);
        }

        if (is_array($payloadOrContract) && is_string($schemaOrPayload)) {
            return $this->validateSchemaPathPayload($payloadOrContract, $schemaOrPayload);
        }

        throw new InvalidArgumentException(
            'SimpleJsonSchemaValidator::validate expects either (array $payload, string $schemaPath) '
            .'or (string $contract, array $payload).',
        );
    }

    /**
     * Validate the given payload against the provided schema definition.
     *
     * @return array<int, string>
     */
    public function validateInline(array $payload, array $schema, ?array $rootSchema = null): array
    {
        if ($schema === []) {
            return ['Schema definition cannot be empty.'];
        }

        $root = $rootSchema ?? $schema;

        return $this->validateAgainstSchema($payload, $schema, '$', $root);
    }

    /**
     * Recursively validate the payload using the provided schema definition.
     *
     * @param  array<string, mixed>|mixed  $data
     * @param  array<string, mixed>  $schema
     * @param  array<string, mixed>  $rootSchema
     * @return array<int, string>
     */
    private function validateAgainstSchema(mixed $data, array $schema, string $path, array $rootSchema): array
    {
        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            $schema = $this->resolveReference($schema['$ref'], $rootSchema) ?? [];
        }

        $errors = [];

        if (isset($schema['type'])) {
            $errors = array_merge($errors, $this->validateType($data, $schema['type'], $path));
            if ($errors !== []) {
                return $errors;
            }
        }

        if (isset($schema['const']) && $data !== $schema['const']) {
            $errors[] = sprintf('%s must equal %s.', $path, json_encode($schema['const']));
        }

        if (isset($schema['pattern']) && is_string($schema['pattern']) && is_string($data)) {
            if (@preg_match('/'.$schema['pattern'].'/', '') === false) {
                $errors[] = sprintf('%s has an invalid pattern definition.', $path);
            } elseif (! preg_match('/'.$schema['pattern'].'/u', $data)) {
                $errors[] = sprintf('%s does not match the required pattern.', $path);
            }
        }

        if (isset($schema['minimum']) && is_numeric($schema['minimum']) && is_numeric($data)) {
            if ((float) $data < (float) $schema['minimum']) {
                $errors[] = sprintf('%s must be greater than or equal to %s.', $path, $schema['minimum']);
            }
        }

        if (isset($schema['properties']) && is_array($schema['properties']) && is_array($data)) {
            foreach ($schema['properties'] as $property => $definition) {
                if (array_key_exists($property, $data)) {
                    $errors = array_merge(
                        $errors,
                        $this->validateAgainstSchema($data[$property], (array) $definition, $path.'.'.$property, $rootSchema)
                    );
                }
            }
        }

        if (isset($schema['required']) && is_array($schema['required']) && is_array($data)) {
            foreach ($schema['required'] as $requiredProperty) {
                if (! array_key_exists($requiredProperty, $data)) {
                    $errors[] = sprintf('%s.%s is required.', $path, $requiredProperty);
                }
            }
        }

        if (isset($schema['additionalProperties']) && $schema['additionalProperties'] === false && is_array($data)) {
            $allowed = isset($schema['properties']) && is_array($schema['properties'])
                ? array_keys($schema['properties'])
                : [];

            foreach (array_keys($data) as $key) {
                if (! in_array($key, $allowed, true)) {
                    $errors[] = sprintf('%s.%s is not allowed by the schema.', $path, $key);
                }
            }
        }

        if (isset($schema['items']) && is_array($schema['items']) && is_array($data)) {
            foreach (array_values($data) as $index => $item) {
                $errors = array_merge(
                    $errors,
                    $this->validateAgainstSchema($item, $schema['items'], sprintf('%s[%d]', $path, $index), $rootSchema)
                );
            }
        }

        if (isset($schema['anyOf']) && is_array($schema['anyOf'])) {
            $anyOfPassed = false;
            foreach ($schema['anyOf'] as $anyOfSchema) {
                $result = $this->validateAgainstSchema($data, (array) $anyOfSchema, $path, $rootSchema);
                if ($result === []) {
                    $anyOfPassed = true;
                    break;
                }
            }

            if (! $anyOfPassed) {
                $errors[] = sprintf('%s must satisfy at least one anyOf constraint.', $path);
            }
        }

        if (isset($schema['format']) && is_string($schema['format'])) {
            $errors = array_merge($errors, $this->validateFormat($data, $schema['format'], $path));
        }

        return $errors;
    }

    private function resolveReference(string $reference, array $schema): ?array
    {
        if (! str_starts_with($reference, '#/')) {
            return null;
        }

        $path = Str::of($reference)->after('#/')->explode('/');
        $node = Collection::make($schema);

        foreach ($path as $segment) {
            if ($node instanceof Collection) {
                $node = $node->get($segment);
            } elseif (is_array($node) && array_key_exists($segment, $node)) {
                $node = $node[$segment];
            } else {
                return null;
            }
        }

        return is_array($node) ? $node : null;
    }

    private function validateType(mixed $data, string|array $type, string $path): array
    {
        $types = (array) $type;

        foreach ($types as $candidate) {
            if ($this->valueMatchesType($data, $candidate)) {
                return [];
            }
        }

        return [sprintf('%s must be of type %s.', $path, implode('|', $types))];
    }

    private function valueMatchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'object' => is_array($value),
            'array' => is_array($value),
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            'null' => $value === null,
            default => true,
        };
    }

    private function validateFormat(mixed $value, string $format, string $path): array
    {
        if (! is_string($value)) {
            return [];
        }

        return match ($format) {
            'uri' => filter_var($value, FILTER_VALIDATE_URL) ? [] : [sprintf('%s must be a valid URI.', $path)],
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? [] : [sprintf('%s must be a valid email address.', $path)],
            'date-time' => strtotime($value) !== false ? [] : [sprintf('%s must be a valid date-time string.', $path)],
            default => [],
        };
    }

    /**
     * Validate a payload using the schema located at the provided path.
     *
     * @param  array<string, mixed>|list<mixed>  $payload
     * @return array<int, string>
     */
    private function validateSchemaPathPayload(array $payload, string $schemaPath): array
    {
        $schemaResult = $this->loadSchema($schemaPath);
        if ($schemaResult['errors'] !== []) {
            return $schemaResult['errors'];
        }

        /** @var array<string, mixed> $schema */
        $schema = $schemaResult['schema'];

        return $this->validateAgainstSchema($payload, $schema, '$', $schema);
    }

    /**
     * Validate a payload against a contract definition stored under the schema's $defs section.
     *
     * @param  array<string, mixed>|list<mixed>  $payload
     * @return array<int, string>
     */
    private function validateContractPayload(string $contract, array $payload): array
    {
        $schemaPath = $this->schemaPathForContract($contract);

        if ($schemaPath === null) {
            return [sprintf('Schema for contract [%s] is not registered.', $contract)];
        }

        $schemaResult = $this->loadSchema($schemaPath);
        if ($schemaResult['errors'] !== []) {
            return $schemaResult['errors'];
        }

        /** @var array<string, mixed> $schema */
        $schema = $schemaResult['schema'];

        $definitionKey = $this->definitionKeyForContract($contract);
        $definition = $schema['$defs'][$definitionKey] ?? null;

        if (! is_array($definition)) {
            return [sprintf('Schema for contract [%s] is missing the [%s] definition.', $contract, $definitionKey)];
        }

        return $this->validateAgainstSchema($payload, $definition, '$', $schema);
    }

    /**
     * @return array{schema: array<string, mixed>|null, errors: array<int, string>}
     */
    private function loadSchema(string $schemaPath): array
    {
        if (array_key_exists($schemaPath, $this->schemaCache)) {
            return ['schema' => $this->schemaCache[$schemaPath], 'errors' => []];
        }

        if (! $this->filesystem->exists($schemaPath)) {
            return ['schema' => null, 'errors' => [sprintf('Schema file [%s] could not be located.', $schemaPath)]];
        }

        $contents = $this->filesystem->get($schemaPath);
        $decoded = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return ['schema' => null, 'errors' => [sprintf('Schema file [%s] does not contain a valid JSON object.', $schemaPath)]];
        }

        $this->schemaCache[$schemaPath] = $decoded;

        return ['schema' => $decoded, 'errors' => []];
    }

    private function schemaPathForContract(string $contract): ?string
    {
        return match (strtolower($contract)) {
            ProductContract::CONTRACT => ProductContract::schemaPath(),
            CategoryContract::CONTRACT => CategoryContract::schemaPath(),
            BrandContract::CONTRACT => BrandContract::schemaPath(),
            OrderContract::CONTRACT => OrderContract::schemaPath(),
            UserContract::CONTRACT => UserContract::schemaPath(),
            default => null,
        };
    }

    private function definitionKeyForContract(string $contract): string
    {
        return match (strtolower($contract)) {
            ProductContract::CONTRACT => 'product',
            CategoryContract::CONTRACT => 'category',
            BrandContract::CONTRACT => 'brand',
            OrderContract::CONTRACT => 'order',
            UserContract::CONTRACT => 'user',
            default => $contract,
        };
    }
}
