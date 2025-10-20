<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Contracts\SimpleJsonSchemaValidator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use function base_path;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;

final class ValidateContractCommand extends Command
{
    protected $signature = 'contracts:validate {entity : Contract entity name (product, category, brand, order, user)} {file : Path to JSON file}';

    protected $description = 'Validate a JSON payload against the versioned contract schema.';

    public function __construct(
        private readonly SimpleJsonSchemaValidator $validator,
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $entity = (string) $this->argument('entity');
        $file = (string) $this->argument('file');
        $path = base_path($file);

        if (! $this->filesystem->exists($path)) {
            $this->components->error("JSON file not found at {$file}.");

            return self::FAILURE;
        }

        $payload = $this->filesystem->get($path);
        $decoded = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $message = json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : 'Invalid JSON structure.';
            $this->components->error("Unable to decode JSON: {$message}");

            return self::FAILURE;
        }

        try {
            $errors = $this->validator->validate($entity, $decoded);
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $this->components->info('Payload is valid.');

        return self::SUCCESS;
    }
}
