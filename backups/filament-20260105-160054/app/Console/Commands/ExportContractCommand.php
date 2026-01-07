<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DataTransfer\DataTransferManager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ExportContractCommand extends Command
{
    protected $signature = 'export:contract {contract : Data transfer contract key} {--format=json : Output format} {--filename= : Optional file name within the exports directory}';

    protected $description = 'Export data for a configured contract into storage/app/exports.';

    public function handle(DataTransferManager $manager): int
    {
        $contractKey = Str::lower((string) $this->argument('contract'));
        $contract = $manager->resolve($contractKey);

        $format = Str::lower((string) $this->option('format'));
        if ($format === '') {
            throw new InvalidArgumentException('A format must be provided.');
        }

        if (! in_array($format, $contract->supportedFormats(), true)) {
            throw new InvalidArgumentException("Unsupported format [{$format}] for contract [{$contractKey}].");
        }

        $filename = $this->option('filename');
        $filename = is_string($filename) && $filename !== ''
            ? $filename
            : sprintf('%s-%s.%s', $contractKey, now()->format('Ymd_His'), $format);

        if (! Str::endsWith($filename, ".{$format}")) {
            $filename .= ".{$format}";
        }

        $this->assertValidFileName($filename);

        $diskName = (string) config('data-transfer.disk', 'local');
        $disk = Storage::disk($diskName);
        if (! $disk instanceof FilesystemAdapter) {
            throw new InvalidArgumentException("Configured disk [{$diskName}] is not a local filesystem adapter.");
        }

        $exportsPath = trim((string) config('data-transfer.exports_path', 'exports'), '/');
        if ($exportsPath === '') {
            throw new InvalidArgumentException('The exports path configuration cannot be empty.');
        }

        if (! $disk->exists($exportsPath)) {
            $disk->makeDirectory($exportsPath);
        }

        $relativePath = $exportsPath . '/' . $filename;
        $contract->export($format, $disk, $relativePath);

        $absolutePath = $disk->path($relativePath);

        Log::channel((string) config('data-transfer.log_channel', 'maintenance'))
            ->info('Data contract export completed.', [
                'contract' => $contractKey,
                'format'   => $format,
                'path'     => $absolutePath,
            ]);

        $this->components->info("Export created: {$absolutePath}");

        return self::SUCCESS;
    }

    private function assertValidFileName(string $filename): void
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            throw new InvalidArgumentException('The file name may only contain letters, numbers, dashes, underscores, and dots.');
        }
    }
}
