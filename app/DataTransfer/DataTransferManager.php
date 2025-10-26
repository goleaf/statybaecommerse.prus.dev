<?php

declare(strict_types=1);

namespace App\DataTransfer;

use App\DataTransfer\Contracts\DataTransferContract;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class DataTransferManager
{
    public function __construct(private readonly Container $container) {}

    public function resolve(string $key): DataTransferContract
    {
        $contracts = config('data-transfer.contracts', []);
        if (! is_array($contracts) || ! array_key_exists($key, $contracts)) {
            throw new InvalidArgumentException("Unknown data transfer contract [{$key}].");
        }

        $class = $contracts[$key];
        $instance = $this->container->make($class);

        if (! $instance instanceof DataTransferContract) {
            throw new InvalidArgumentException("Configured contract [{$key}] does not implement DataTransferContract.");
        }

        return $instance;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        $contracts = config('data-transfer.contracts', []);

        return is_array($contracts) ? array_keys($contracts) : [];
    }
}
