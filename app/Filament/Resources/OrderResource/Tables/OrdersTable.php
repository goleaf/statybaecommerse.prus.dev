<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Tables;

use App\Enums\ExportType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Actions\RequestExportBulkAction;
use BackedEnum;
use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('messages.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label(__('messages.customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable()
                    ->label(__('messages.status'))
                    ->formatStateUsing(static fn ($state): string => OrderStatus::tryFrom(self::normalizeEnumValue($state))?->label() ?? Str::headline(self::normalizeEnumValue($state)))
                    ->badge()
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('messages.total'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->sortable()
                    ->label(__('messages.payment_status'))
                    ->formatStateUsing(static fn ($state): string => PaymentStatus::tryFrom(self::normalizeEnumValue($state))?->getLabel() ?? Str::headline(self::normalizeEnumValue($state)))
                    ->badge()
                    ->searchable(),
                TextColumn::make('currentInvoice.status')
                    ->label('Invoice')
                    ->formatStateUsing(static fn ($state): string => $state !== null ? Str::headline((string) $state) : '-')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_option.name')
                    ->label(__('messages.shipping'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tracking_number')
                    ->sortable()
                    ->label(__('messages.tracking_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                \Filament\Tables\Actions\Action::make('sendToVenipak')
                    ->label('Send to Venipak')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Send Order to Venipak')
                    ->modalDescription('Are you sure you want to dispatch this order to Venipak?')
                    ->action(function (\App\Models\Order $record) {
                        try {
                            $service = app(\App\Services\VenipakService::class);
                            $response = $service->dispatchOrder($record, 1);

                            // Save tracking to shipping metadata
                            if ($shipping = $record->shipping) {
                                $meta = $shipping->metadata ?? [];
                                $meta['venipak_tracking'] = $response['tracking_numbers'];
                                $meta['venipak_manifest'] = $response['manifest_id'];
                                $shipping->metadata = $meta;
                                $shipping->tracking_number = implode(', ', $response['tracking_numbers']);
                                $shipping->save();
                            }

                            \Filament\Notifications\Notification::make()->title('Order dispatched to Venipak successfully!')->success()->send();
                        } catch (Exception $e) {
                            \Filament\Notifications\Notification::make()->title('Failed to dispatch to Venipak')->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([ // Changed to bulkActions as it makes more sense for BulkActionGroup
                RequestExportBulkAction::make(ExportType::ORDERS),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function normalizeEnumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if (is_scalar($value) || $value === null) {
            return (string) ($value ?? '');
        }

        return '';
    }
}
