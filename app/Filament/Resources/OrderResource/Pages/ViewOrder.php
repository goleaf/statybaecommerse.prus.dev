<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderInvoice;
use App\Services\Invoices\OrderInvoiceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateInvoicePdf')
                ->label(__('messages.generate_invoice_pdf'))
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->visible(static fn (): bool => (bool) config('invoices.enabled', false))
                ->form([
                    Select::make('invoice_type')
                        ->label(__('messages.invoice_type'))
                        ->options(OrderInvoiceService::invoiceTypeOptions())
                        ->required()
                        ->default((string) config('invoices.default_invoice_type', 'sf')),
                ])
                ->action(function (array $data): void {
                    $order = $this->getRecord();

                    if (! $order instanceof Order) {
                        return;
                    }

                    try {
                        $invoice = app(OrderInvoiceService::class)->generateForOrder(
                            $order->withoutRelations(),
                            true,
                            OrderInvoice::MODE_MANUAL,
                            is_string($data['invoice_type'] ?? null) ? $data['invoice_type'] : null,
                        );

                        if (! $invoice instanceof OrderInvoice) {
                            Notification::make()
                                ->title(__('messages.invoice_generation_not_available'))
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('messages.invoice_generation_completed'))
                            ->success()
                            ->actions(
                                $invoice->downloadUrl() !== null
                                    ? [
                                        Action::make('downloadInvoicePdf')
                                            ->label(__('messages.download_pdf'))
                                            ->url((string) $invoice->downloadUrl(), shouldOpenInNewTab: true)
                                            ->markAsRead(),
                                    ]
                                    : []
                            )
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title(__('messages.invoice_generation_failed'))
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('downloadCurrentInvoicePdf')
                ->label(__('messages.download_current_invoice'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (): bool => $this->resolveCurrentInvoiceDownloadUrl() !== null)
                ->url(fn (): string => (string) $this->resolveCurrentInvoiceDownloadUrl(), shouldOpenInNewTab: true),
            EditAction::make(),
        ];
    }

    private function resolveCurrentInvoiceDownloadUrl(): ?string
    {
        $order = $this->getRecord();

        if (! $order instanceof Order) {
            return null;
        }

        $order->loadMissing('currentInvoice.file');

        return $order->currentInvoice?->downloadUrl();
    }
}
