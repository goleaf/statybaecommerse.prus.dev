<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Services\Invoices\OrderInvoiceService;
use Illuminate\Console\Command;
use Throwable;

final class BackfillOrderInvoicesCommand extends Command
{
    protected $signature = 'orders:invoices:backfill
        {--force}
        {--invoice-type=}
        {--order-id= : Generate invoice only for one order ID}
        {--allow-unpaid : Allow generation for unpaid target order (manual mode)}';

    protected $description = '';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription(__('messages.invoices_backfill_description'));
    }

    public function handle(OrderInvoiceService $service): int
    {
        if (! (bool) config('invoices.enabled', false)) {
            $this->warn(__('messages.invoices_backfill_disabled'));

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $allowUnpaid = (bool) $this->option('allow-unpaid');
        $orderId = $this->parseOrderIdOption();

        if ($this->option('order-id') !== null && $orderId === null) {
            $this->error(__('messages.invoices_backfill_invalid_order_id'));

            return self::FAILURE;
        }

        if ($allowUnpaid && $orderId === null) {
            $this->error(__('messages.invoices_backfill_allow_unpaid_requires_order_id'));

            return self::FAILURE;
        }

        $requestedInvoiceType = $this->option('invoice-type');
        $invoiceType = is_string($requestedInvoiceType) && trim($requestedInvoiceType) !== ''
            ? strtolower(trim($requestedInvoiceType))
            : null;

        if ($invoiceType !== null && ! in_array($invoiceType, OrderInvoiceService::allowedInvoiceTypes(), true)) {
            $this->error(__('messages.invalid_invoice_type_option', [
                'type'    => $invoiceType,
                'allowed' => implode(', ', OrderInvoiceService::allowedInvoiceTypes()),
            ]));

            return self::FAILURE;
        }

        $processed = 0;
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        $query = Order::query()
            ->withoutGlobalScopes()
            ->orderBy('id');

        if ($orderId !== null) {
            $query->whereKey($orderId);
        }

        if (! $allowUnpaid) {
            $query->whereIn('payment_status', ['paid', 'captured', 'settled']);
        }

        $mode = $allowUnpaid ? OrderInvoice::MODE_MANUAL : OrderInvoice::MODE_BACKFILL;

        $total = (clone $query)->count();
        $this->info(__('messages.invoices_backfill_start', ['count' => $total]));

        $query->chunkById(100, function ($orders) use (
            $service,
            $force,
            $invoiceType,
            $mode,
            &$processed,
            &$generated,
            &$skipped,
            &$failed
        ): void {
            foreach ($orders as $order) {
                $processed++;

                try {
                    $invoice = $service->generateForOrder($order, $force, $mode, $invoiceType);

                    if ($invoice instanceof OrderInvoice) {
                        $generated++;
                        $this->line(__('messages.invoices_backfill_generated', ['order' => (string) $order->number]));
                    } else {
                        $skipped++;
                        $this->line(__('messages.invoices_backfill_skipped', ['order' => (string) $order->number]));
                    }
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error(__('messages.invoices_backfill_failed', [
                        'order' => (string) $order->number,
                        'error' => $exception->getMessage(),
                    ]));
                }
            }
        });

        $this->newLine();
        $this->info(__('messages.invoices_backfill_finished', [
            'processed' => $processed,
            'generated' => $generated,
            'skipped'   => $skipped,
            'failed'    => $failed,
        ]));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
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
}
