<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account\Orders;

use App\Models\Order;
use App\Models\OrderInvoice;
use App\Services\Invoices\OrderInvoiceService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Order Detail
 *
 * Livewire component for displaying order details with reactive frontend functionality.
 */
final class Detail extends Component
{
    public Order $order;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(string $number): void
    {
        $query = Order::with([
            'items',
            'items.product',
            'items.productVariant',
            'shipping',
            'invoices.file',
            'currentInvoice.file',
        ])
            ->where('number', $number)
            ->where('user_id', auth()->id());

        $this->order = $query->firstOrFail();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.account.orders.detail', [
            'documentsByType' => $this->resolveDocumentsByType(),
        ])
            ->layout('components.layouts.templates.account')
            ->title(__('frontend.account.order_detail.title'));
    }

    /**
     * @return Collection<string, Collection<int, OrderInvoice>>
     */
    private function resolveDocumentsByType(): Collection
    {
        $knownTypes = OrderInvoiceService::allowedInvoiceTypes();

        /** @var Collection<int, OrderInvoice> $documents */
        $documents = $this->order->invoices
            ->sortByDesc(function (OrderInvoice $invoice): int {
                return $invoice->generated_at?->getTimestamp()
                    ?? $invoice->created_at?->getTimestamp()
                    ?? 0;
            })
            ->values();

        $groupedKnown = collect($knownTypes)->mapWithKeys(
            fn (string $type): array => [
                $type => $documents
                    ->filter(
                        fn (OrderInvoice $invoice): bool => strtolower(trim((string) $invoice->invoice_type)) === $type
                    )
                    ->values(),
            ]
        );

        /** @var Collection<string, Collection<int, OrderInvoice>> $groupedUnknown */
        $groupedUnknown = $documents
            ->filter(function (OrderInvoice $invoice) use ($knownTypes): bool {
                $type = strtolower(trim((string) $invoice->invoice_type));

                return $type === '' || ! in_array($type, $knownTypes, true);
            })
            ->groupBy(
                fn (OrderInvoice $invoice): string => strtolower(trim((string) $invoice->invoice_type)) ?: 'other'
            )
            ->sortKeys()
            ->map(
                fn (Collection $rows): Collection => $rows
                    ->filter(fn ($row): bool => $row instanceof OrderInvoice)
                    ->values()
            );

        return $groupedKnown->merge($groupedUnknown);
    }
}
