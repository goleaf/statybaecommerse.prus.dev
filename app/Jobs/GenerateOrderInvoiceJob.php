<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Services\Invoices\OrderInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GenerateOrderInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(
        private readonly int $orderId,
        private readonly bool $force = false,
        private readonly string $mode = OrderInvoice::MODE_AUTO,
        private readonly ?string $invoiceType = null,
    ) {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 180, 300, 600];
    }

    /**
     * @throws Throwable
     */
    public function handle(OrderInvoiceService $service): void
    {
        $order = Order::query()
            ->withoutGlobalScopes()
            ->with(['items', 'user'])
            ->find($this->orderId);

        if (! $order instanceof Order) {
            return;
        }

        $service->generateForOrder($order, $this->force, $this->mode, $this->invoiceType);
    }
}
