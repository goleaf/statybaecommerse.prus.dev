<?php

declare(strict_types=1);

namespace App\Support\Backup;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

/**
 * @phpstan-type RepositoryClass class-string
 */
final class RepositoryRegistry
{
    /**
     * @param array<string, RepositoryClass> $definitions
     */
    private function __construct(
        private readonly Container $container,
        private readonly array $definitions,
    ) {}

    public static function fromConfig(Container $container): self
    {
        $configured = config('backup.repositories');

        if ($configured === null) {
            return new self($container, self::defaultDefinitions());
        }

        /** @var mixed $configured */
        return new self($container, self::normalizeDefinitions($configured));
    }

    /**
     * @param array<string, string> $definitions
     */
    public static function fromDefinitions(Container $container, array $definitions): self
    {
        return new self($container, self::normalizeDefinitions($definitions, allowEmpty: true));
    }

    /**
     * @return array<string, RepositoryClass>
     */
    public function definitions(): array
    {
        return $this->definitions;
    }

    public function isEmpty(): bool
    {
        return $this->definitions === [];
    }

    /**
     * @return array<string, object>
     */
    public function instantiate(): array
    {
        if ($this->definitions === []) {
            return [];
        }

        $instances = [];

        foreach ($this->definitions as $label => $class) {
            if (! class_exists($class)) {
                throw new RuntimeException(sprintf('Backup repository class [%s] does not exist.', $class));
            }

            $instance = $this->container->make($class);

            if (! method_exists($instance, 'count')) {
                throw new RuntimeException(sprintf('Backup repository [%s] must define a count method.', $class));
            }

            $instances[$label] = $instance;
        }

        return $instances;
    }

    /**
     * @return array<string, int>
     */
    public function counts(string $connection): array
    {
        $instances = $this->instantiate();

        if ($instances === []) {
            return [];
        }

        $counts = [];

        foreach ($instances as $label => $instance) {
            $counts[$label] = $this->resolveCount($instance, $connection);
        }

        return $counts;
    }

    /**
     * @return array<string, RepositoryClass>
     */
    private static function defaultDefinitions(): array
    {
        return [
            'users'    => UserRepository::class,
            'products' => ProductRepository::class,
        ];
    }

    /**
     * @param  array<string, mixed>|null      $definitions
     * @return array<string, RepositoryClass>
     */
    private static function normalizeDefinitions($definitions, bool $allowEmpty = false): array
    {
        if (! is_array($definitions)) {
            if ($allowEmpty) {
                return [];
            }

            throw new RuntimeException('Backup repositories configuration must be an associative array.');
        }

        if ($definitions === []) {
            return [];
        }

        if (array_is_list($definitions)) {
            throw new RuntimeException('Backup repositories configuration must be an associative array.');
        }

        $normalized = [];

        foreach ($definitions as $label => $class) {
            if (! is_string($label) || $label === '') {
                throw new RuntimeException('Backup repository keys must be non-empty strings.');
            }

            if (! is_string($class) || $class === '') {
                throw new RuntimeException(sprintf('Backup repository [%s] must reference a class name.', $label));
            }

            $normalized[$label] = $class;
        }

        return $normalized;
    }

    private function resolveCount(object $repository, string $connection): int
    {
        $count = $repository->count($connection);

        if (is_int($count)) {
            return $count;
        }

        if (is_numeric($count)) {
            return (int) $count;
        }

        throw new RuntimeException(sprintf('Backup repository [%s]::count() must return an integer.', $repository::class));
    }
}
