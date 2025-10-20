<?php

declare(strict_types=1);

namespace App\Support\Contracts;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class SimpleJsonSchemaValidator
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function validate(string $entity, mixed $payload): array
    {
        $schema = $this->getSchema($entity);

        return $this->validateAgainstSchema($schema, $payload, '$');
    }

    /**
     * @return array<int, string>
     */
    private function validateAgainstSchema(array $schema, mixed $value, string $path): array
    {
        if (array_key_exists('$ref', $schema)) {
            $referencedSchema = $this->resolveReference($schema['$ref']);

            return $this->validateAgainstSchema($referencedSchema, $value, $path);
        }

        $errors = [];

        $type = $schema['type'] ?? null;
        if ($type !== null && ! $this->isTypeSatisfied($type, $value)) {
            $expected = is_array($type) ? implode('|', $type) : (string) $type;
            $errors[] = sprintf('%s is expected to be of type %s.', $path, $expected);

            return $errors;
        }

        if (isset($schema['enum']) && ! in_array($value, $schema['enum'], true)) {
            $errors[] = sprintf('%s is not one of the allowed values.', $path);
        }

        if (isset($schema['required']) && is_array($schema['required']) && is_array($value)) {
            foreach ($schema['required'] as $requiredKey) {
                if (! array_key_exists($requiredKey, $value)) {
                    $errors[] = sprintf('%s.%s is required.', $path, $requiredKey);
                }
            }
        }

        if (isset($schema['properties']) && is_array($schema['properties']) && is_array($value)) {
            foreach ($schema['properties'] as $key => $propertySchema) {
                if (array_key_exists($key, $value)) {
                    $errors = array_merge($errors, $this->validateAgainstSchema($propertySchema, $value[$key], $path.'.'.$key));
                }
            }
            if (($schema['additionalProperties'] ?? true) === false) {
                $allowed = array_keys($schema['properties']);
                foreach (array_keys($value) as $propKey) {
                    if (! in_array($propKey, $allowed, true)) {
                        $errors[] = sprintf('%s.%s is not an allowed property.', $path, $propKey);
                    }
                }
            }
        }

        if (($schema['type'] ?? null) === 'array' && is_array($value)) {
            $itemsSchema = $schema['items'] ?? null;
            if ($itemsSchema !== null) {
                foreach ($value as $index => $item) {
                    $errors = array_merge($errors, $this->validateAgainstSchema($itemsSchema, $item, $path.'['.$index.']'));
                }
            }
            if (isset($schema['minItems']) && count($value) < (int) $schema['minItems']) {
                $errors[] = sprintf('%s must contain at least %d item(s).', $path, $schema['minItems']);
            }
        }

        if (($schema['type'] ?? null) === 'string' && is_string($value)) {
            if (isset($schema['minLength']) && Str::length($value) < (int) $schema['minLength']) {
                $errors[] = sprintf('%s must be at least %d characters.', $path, $schema['minLength']);
            }
            if (($schema['format'] ?? null) === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = sprintf('%s must be a valid e-mail address.', $path);
            }
            if (($schema['format'] ?? null) === 'uri' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = sprintf('%s must be a valid URI.', $path);
            }
            if (($schema['format'] ?? null) === 'date-time' && ! $this->isValidDateTime($value)) {
                $errors[] = sprintf('%s must be a valid RFC3339 date-time string.', $path);
            }
            if (isset($schema['pattern']) && ! preg_match('/'.$schema['pattern'].'/', $value)) {
                $errors[] = sprintf('%s does not match required pattern.', $path);
            }
        }

        if (($schema['type'] ?? null) === 'integer' && is_int($value)) {
            if (isset($schema['minimum']) && $value < (int) $schema['minimum']) {
                $errors[] = sprintf('%s must be greater than or equal to %d.', $path, $schema['minimum']);
            }
        }

        if (($schema['type'] ?? null) === 'number' && is_numeric($value)) {
            if (isset($schema['minimum']) && $value < (float) $schema['minimum']) {
                $errors[] = sprintf('%s must be greater than or equal to %s.', $path, $schema['minimum']);
            }
        }

        if (($schema['type'] ?? null) === 'object' && is_array($value) && isset($schema['additionalProperties']) && is_array($schema['additionalProperties'])) {
            $additionalSchema = $schema['additionalProperties'];
            $knownProperties = array_keys($schema['properties'] ?? []);
            foreach ($value as $key => $propertyValue) {
                if (! in_array($key, $knownProperties, true)) {
                    $errors = array_merge($errors, $this->validateAgainstSchema($additionalSchema, $propertyValue, $path.'.'.$key));
                }
            }
        }

        return $errors;
    }

    private function isTypeSatisfied(string|array $expectedType, mixed $value): bool
    {
        $types = Arr::wrap($expectedType);

        foreach ($types as $type) {
            $matches = match ($type) {
                'integer' => is_int($value),
                'number' => is_int($value) || is_float($value),
                'string' => is_string($value),
                'boolean' => is_bool($value),
                'object' => is_array($value),
                'array' => is_array($value),
                'null' => $value === null,
                default => true,
            };

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    private function isValidDateTime(string $value): bool
    {
        $date = date_create($value);

        return $date !== false;
    }

    private function getSchema(string $entity): array
    {
        $mapping = config('contracts.entities');
        $entityKey = strtolower($entity);
        $info = $mapping[$entityKey] ?? null;
        if ($info === null) {
            throw new \InvalidArgumentException(sprintf('Unknown contract entity [%s].', $entity));
        }

        $schemaPath = base_path($info['schema']);
        if (! $this->filesystem->exists($schemaPath)) {
            throw new \RuntimeException(sprintf('Schema file not found at [%s].', $schemaPath));
        }

        $content = $this->filesystem->get($schemaPath);
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException(sprintf('Invalid JSON schema for entity [%s].', $entity));
        }

        return $decoded;
    }

    private function resolveReference(string $reference): array
    {
        $mapping = config('contracts.entities');
        foreach ($mapping as $entity => $info) {
            if (($info['schema'] ?? null) === null) {
                continue;
            }

            if (str_contains($reference, $info['schema'])) {
                $schemaPath = base_path($info['schema']);
                $content = $this->filesystem->get($schemaPath);
                $decoded = json_decode($content, true);
                if (! is_array($decoded)) {
                    break;
                }

                return $decoded;
            }
        }

        throw new \RuntimeException(sprintf('Unable to resolve schema reference [%s].', $reference));
    }
}
