<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\File;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Models\User;
use App\Support\Storage\SecureStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File as FileSystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class LinkOrderInvoicePdfsCommand extends Command
{
    protected $signature = 'orders:invoices:link-pdfs
        {--dry-run : Preview links without writing}
        {--order-id= : Limit to a single order ID}
        {--report-path= : Override unresolved legacy report CSV path}';

    protected $description = '';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription(__('messages.invoices_link_pdfs_description'));
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $orderId = $this->parseOrderIdOption();
        $reportPath = is_string($this->option('report-path')) ? trim((string) $this->option('report-path')) : '';

        if ($this->option('order-id') !== null && $orderId === null) {
            $this->error(__('messages.invoices_link_pdfs_invalid_order_id'));

            return self::FAILURE;
        }

        $query = File::query()
            ->where('fileable_type', Order::class)
            ->where('mime_type', 'application/pdf')
            ->whereNotIn('id', OrderInvoice::query()->select('file_id')->whereNotNull('file_id'))
            ->orderBy('id');

        if ($orderId !== null) {
            $query->where('fileable_id', $orderId);
        }

        $candidates = (clone $query)->count();
        $this->info(__('messages.invoices_link_pdfs_start', ['count' => $candidates]));

        $processed = 0;
        $linked = 0;
        $skipped = 0;
        $affectedOrderIds = [];

        $query->chunkById(200, function ($files) use ($dryRun, &$processed, &$linked, &$skipped, &$affectedOrderIds): void {
            foreach ($files as $file) {
                $processed++;

                if (! is_numeric($file->fileable_id)) {
                    $skipped++;
                    $this->line(__('messages.invoices_link_pdfs_skipped', ['file' => (string) $file->id]));

                    continue;
                }

                $resolvedOrderId = (int) $file->fileable_id;
                if ($resolvedOrderId <= 0) {
                    $skipped++;
                    $this->line(__('messages.invoices_link_pdfs_skipped', ['file' => (string) $file->id]));

                    continue;
                }

                $orderExists = Order::query()->withoutGlobalScopes()->whereKey($resolvedOrderId)->exists();
                if (! $orderExists) {
                    $skipped++;
                    $this->line(__('messages.invoices_link_pdfs_skipped', ['file' => (string) $file->id]));

                    continue;
                }

                $affectedOrderIds[$resolvedOrderId] = true;
                $linked++;

                if ($dryRun) {
                    $this->line(__('messages.invoices_link_pdfs_would_link', [
                        'file'  => (string) $file->id,
                        'order' => (string) $resolvedOrderId,
                    ]));

                    continue;
                }

                $this->createOrderInvoiceFromFile($file, $resolvedOrderId, 'legacy-file-link');

                $this->line(__('messages.invoices_link_pdfs_linked', [
                    'file'  => (string) $file->id,
                    'order' => (string) $resolvedOrderId,
                ]));
            }
        });

        $legacyResult = $this->linkResolvableLegacyPdfs($orderId, $dryRun);
        $processed += $legacyResult['processed'];
        $linked += $legacyResult['linked'];
        $skipped += $legacyResult['skipped'];
        $affectedOrderIds += $legacyResult['affected_order_ids'];

        if (! $dryRun) {
            foreach (array_keys($affectedOrderIds) as $affectedOrderId) {
                $this->syncCurrentInvoiceFlag((int) $affectedOrderId);
            }
        }

        $report = $this->writeUnresolvedLegacyReport($legacyResult['unresolved'], $reportPath, $dryRun);

        if ($report['count'] > 0) {
            $this->warn(__('messages.invoices_link_pdfs_unresolved_report', [
                'count' => (string) $report['count'],
                'path'  => (string) ($report['path'] ?? ''),
            ]));
        }

        $this->newLine();
        $this->info(__('messages.invoices_link_pdfs_finished', [
            'processed' => $processed,
            'linked'    => $linked,
            'skipped'   => $skipped,
            'dry_run'   => $dryRun ? 'yes' : 'no',
        ]));

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     processed: int,
     *     linked: int,
     *     skipped: int,
     *     affected_order_ids: array<int, bool>,
     *     unresolved: array<int, array{relative_path: string, size: string, modified_at: string}>
     * }
     */
    private function linkResolvableLegacyPdfs(?int $orderId, bool $dryRun): array
    {
        $disk = Storage::disk(SecureStorage::disk());
        $diskRoot = $disk->path('');
        $documentsPath = $disk->path('documents');

        if (! is_dir($documentsPath)) {
            return [
                'processed'          => 0,
                'linked'             => 0,
                'skipped'            => 0,
                'affected_order_ids' => [],
                'unresolved'         => [],
            ];
        }

        $processed = 0;
        $linked = 0;
        $skipped = 0;
        $affectedOrderIds = [];
        $unresolved = [];

        foreach (FileSystem::allFiles($documentsPath) as $legacyFile) {
            if (strtolower((string) $legacyFile->getExtension()) !== 'pdf') {
                continue;
            }

            $relativePath = str_replace('\\', '/', ltrim(Str::after($legacyFile->getPathname(), $diskRoot), '\\/'));
            if ($relativePath === '') {
                continue;
            }

            if (File::query()->where('path', $relativePath)->exists()) {
                continue;
            }

            $processed++;

            $legacyOrderId = $this->parseLegacyOrderIdFromFilename((string) $legacyFile->getFilename());
            if ($legacyOrderId === null) {
                $unresolved[] = [
                    'relative_path' => $relativePath,
                    'size'          => (string) $legacyFile->getSize(),
                    'modified_at'   => date('c', $legacyFile->getMTime()),
                ];
                $skipped++;

                continue;
            }

            if ($orderId !== null && $legacyOrderId !== $orderId) {
                continue;
            }

            $order = Order::query()->withoutGlobalScopes()->find($legacyOrderId);
            if (! $order instanceof Order) {
                $unresolved[] = [
                    'relative_path' => $relativePath,
                    'size'          => (string) $legacyFile->getSize(),
                    'modified_at'   => date('c', $legacyFile->getMTime()),
                ];
                $skipped++;

                continue;
            }

            $affectedOrderIds[$legacyOrderId] = true;
            $linked++;

            if ($dryRun) {
                $this->line(__('messages.invoices_link_pdfs_would_link', [
                    'file'  => $relativePath,
                    'order' => (string) $legacyOrderId,
                ]));

                continue;
            }

            $file = File::query()->create([
                'name'          => (string) $legacyFile->getFilename(),
                'original_name' => (string) $legacyFile->getFilename(),
                'path'          => $relativePath,
                'disk'          => SecureStorage::disk(),
                'mime_type'     => 'application/pdf',
                'size'          => (int) $legacyFile->getSize(),
                'hash'          => hash_file('sha256', $legacyFile->getPathname()) ?: hash('sha256', $relativePath),
                'fileable_type' => Order::class,
                'fileable_id'   => $legacyOrderId,
                'uploaded_by'   => $this->resolveUploaderId($legacyOrderId),
                'metadata'      => [
                    'source'      => 'legacy-disk-import',
                    'legacy_path' => $relativePath,
                ],
                'created_at' => now()->setTimestamp($legacyFile->getMTime()),
                'updated_at' => now(),
            ]);

            $this->createOrderInvoiceFromFile($file, $legacyOrderId, 'legacy-disk-link');
            $this->line(__('messages.invoices_link_pdfs_linked', [
                'file'  => $relativePath,
                'order' => (string) $legacyOrderId,
            ]));
        }

        return [
            'processed'          => $processed,
            'linked'             => $linked,
            'skipped'            => $skipped,
            'affected_order_ids' => $affectedOrderIds,
            'unresolved'         => $unresolved,
        ];
    }

    private function parseOrderIdOption(): ?int
    {
        $option = $this->option('order-id');

        if (! is_scalar($option)) {
            return null;
        }

        $normalized = trim((string) $option);
        if ($normalized === '' || ! ctype_digit($normalized)) {
            return null;
        }

        $orderId = (int) $normalized;

        return $orderId > 0 ? $orderId : null;
    }

    private function syncCurrentInvoiceFlag(int $orderId): void
    {
        $latestId = OrderInvoice::query()
            ->where('order_id', $orderId)
            ->orderByRaw('COALESCE(generated_at, created_at) DESC')
            ->orderByDesc('id')
            ->value('id');

        if (! is_numeric($latestId)) {
            return;
        }

        OrderInvoice::query()
            ->where('order_id', $orderId)
            ->update(['is_current' => false]);

        OrderInvoice::query()
            ->where('id', (int) $latestId)
            ->update(['is_current' => true]);
    }

    /**
     * @return array{count: int, path: string|null}
     */
    private function writeUnresolvedLegacyReport(array $unresolved, string $customReportPath, bool $dryRun): array
    {
        if ($unresolved === []) {
            return ['count' => 0, 'path' => null];
        }

        $reportPath = $customReportPath !== ''
            ? $customReportPath
            : storage_path('app/reports/unresolved-order-invoice-pdfs-' . now()->format('Ymd_His') . '.csv');

        if (! $dryRun) {
            FileSystem::ensureDirectoryExists(dirname($reportPath));
            $handle = fopen($reportPath, 'wb');

            if ($handle !== false) {
                fputcsv($handle, ['relative_path', 'size', 'modified_at']);

                foreach ($unresolved as $row) {
                    fputcsv($handle, [$row['relative_path'], $row['size'], $row['modified_at']]);
                }

                fclose($handle);
            }
        }

        return [
            'count' => count($unresolved),
            'path'  => $dryRun ? $reportPath : (is_file($reportPath) ? $reportPath : null),
        ];
    }

    private function createOrderInvoiceFromFile(File $file, int $orderId, string $source): void
    {
        OrderInvoice::query()->create([
            'order_id'            => $orderId,
            'file_id'             => (int) $file->getKey(),
            'external_invoice_id' => $this->metadataString($file, 'external_invoice_id'),
            'invoice_series'      => $this->metadataString($file, 'series'),
            'invoice_number'      => $this->metadataString($file, 'number'),
            'full_number'         => $this->metadataString($file, 'full_number') ?? pathinfo((string) $file->original_name, PATHINFO_FILENAME),
            'invoice_type'        => $this->metadataString($file, 'invoice_type'),
            'status'              => OrderInvoice::STATUS_READY,
            'is_current'          => false,
            'generation_mode'     => OrderInvoice::MODE_BACKFILL,
            'provider_payload'    => [
                'source'    => $source,
                'file_id'   => (int) $file->getKey(),
                'file_path' => (string) $file->path,
                'file_disk' => (string) $file->disk,
                'file_hash' => $this->nullableString($file->hash),
                'file_size' => (int) $file->size,
                'linked_at' => now()->toIso8601String(),
            ],
            'error_message' => null,
            'generated_at'  => $file->created_at ?? now(),
            'failed_at'     => null,
        ]);
    }

    private function resolveUploaderId(int $orderId): int
    {
        $userId = Order::query()
            ->withoutGlobalScopes()
            ->whereKey($orderId)
            ->value('user_id');

        if (is_numeric($userId) && (int) $userId > 0) {
            return (int) $userId;
        }

        $fallbackUserId = User::query()->withoutGlobalScopes()->value('id');
        if (is_numeric($fallbackUserId) && (int) $fallbackUserId > 0) {
            return (int) $fallbackUserId;
        }

        return 1;
    }

    private function parseLegacyOrderIdFromFilename(string $filename): ?int
    {
        if (! preg_match('/^(\d+)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.pdf$/', $filename, $matches)) {
            return null;
        }

        $orderId = (int) ($matches[1] ?? 0);

        return $orderId > 0 ? $orderId : null;
    }

    private function metadataString(File $file, string $key): ?string
    {
        $metadata = is_array($file->metadata) ? $file->metadata : [];
        $value = $metadata[$key] ?? null;

        return $this->nullableString($value);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
