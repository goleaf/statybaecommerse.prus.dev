<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Jobs\GenerateOrderInvoiceJob;
use App\Models\OrderInvoice;
use App\Services\Invoices\OrderInvoiceService;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.documents');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_number')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('full_number')
                    ->label(__('ui.invoice'))
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('invoice_type')
                    ->label(__('admin.enum_values.types.document_type'))
                    ->formatStateUsing(fn (?string $state): string => $this->formatInvoiceType($state))
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('generated_at')
                    ->label(__('messages.generated'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('error_message')
                    ->label(__('messages.error'))
                    ->limit(80)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label(__('messages.download_pdf'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (OrderInvoice $record): bool => $record->status === OrderInvoice::STATUS_READY && $record->downloadUrl() !== null)
                    ->url(fn (OrderInvoice $record): string => (string) $record->downloadUrl(), shouldOpenInNewTab: true),
                Action::make('regenerate')
                    ->label(__('messages.regenerate'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription(__('messages.invoice_regeneration_modal_description'))
                    ->form([
                        Select::make('invoice_type')
                            ->label(__('messages.invoice_type'))
                            ->options(OrderInvoiceService::invoiceTypeOptions())
                            ->required()
                            ->default((string) config('invoices.default_invoice_type', 'sf')),
                    ])
                    ->action(function (OrderInvoice $record, array $data): void {
                        GenerateOrderInvoiceJob::dispatch(
                            $record->order_id,
                            true,
                            OrderInvoice::MODE_MANUAL,
                            is_string($data['invoice_type'] ?? null) ? $data['invoice_type'] : null,
                        );

                        Notification::make()
                            ->title(__('messages.invoice_regeneration_queued'))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    private function formatInvoiceType(?string $invoiceType): string
    {
        $normalized = is_string($invoiceType) ? strtolower(trim($invoiceType)) : '';
        if ($normalized === '') {
            return '-';
        }

        $key = "enums.invoice_type.{$normalized}";
        $translated = __($key);

        return $translated !== $key ? $translated : Str::upper($normalized);
    }
}
