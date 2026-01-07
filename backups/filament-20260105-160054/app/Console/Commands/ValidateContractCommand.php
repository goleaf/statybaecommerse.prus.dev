<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Contracts\Entities\BrandContract;
use App\Support\Contracts\Entities\CategoryContract;
use App\Support\Contracts\Entities\OrderContract;
use App\Support\Contracts\Entities\ProductContract;
use App\Support\Contracts\Entities\UserContract;
use App\Support\Contracts\SimpleJsonSchemaValidator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

final class ValidateContractCommand extends Command
{
    protected $signature = 'contracts:validate {entity : product|category|brand|order|user} {payload? : Optional JSON payload file path} {--example : Validate the bundled example payload}';

    protected $description = 'Validate a payload against one of the published API contracts.';

    public function handle(SimpleJsonSchemaValidator $validator, Filesystem $filesystem): int
    {
        $entity = strtolower((string) $this->argument('entity'));
        $class = $this->resolveContractClass($entity);

        $schemaPath = $class::schemaPath();
        $payloadPath = $this->option('example') ? $class::examplePath() : $this->argument('payload');

        if (! is_string($payloadPath) || $payloadPath === '') {
            $this->error('Provide a payload path or use the --example flag.');

            return self::FAILURE;
        }

        if (! $filesystem->exists($payloadPath)) {
            $this->error(sprintf('Payload file [%s] was not found.', $payloadPath));

            return self::FAILURE;
        }

        $contents = $filesystem->get($payloadPath);
        $payload = json_decode($contents, true);

        if (! is_array($payload)) {
            $this->error('Payload is not a valid JSON object.');

            return self::FAILURE;
        }

        $errors = $validator->validate($payload, $schemaPath);

        if ($errors === []) {
            $this->info(sprintf('✔ %s payload is valid for %s.', basename($payloadPath), $entity));

            return self::SUCCESS;
        }

        $this->error(sprintf('✘ %s payload has %d validation issue(s):', basename($payloadPath), count($errors)));
        foreach ($errors as $error) {
            $this->line('  • ' . $error);
        }

        return self::FAILURE;
    }

    /**
     * @return class-string
     */
    private function resolveContractClass(string $entity): string
    {
        return match ($entity) {
            'product'  => ProductContract::class,
            'category' => CategoryContract::class,
            'brand'    => BrandContract::class,
            'order'    => OrderContract::class,
            'user'     => UserContract::class,
            default    => throw new InvalidArgumentException(sprintf('Unsupported entity [%s].', $entity)),
        };
    }
}
