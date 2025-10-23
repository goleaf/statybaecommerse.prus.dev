<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings\Tables;

use App\Forms\Components\Flatpickr;
use App\Models\Order;
use App\Models\OrderShipping;
use App\Support\Filament\Components\Flatpickr;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

class OrderShippingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.number')
                    ->label(__('Order'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('carrier_name')
                    ->label(__('Carrier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service')
                    ->label(__('Service'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tracking_number')
                    ->label(__('Tracking number'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->getStateUsing(static function (OrderShipping $record): string {
                        if ($record->delivered_at) {
                            return 'delivered';
                        }

                        if ($record->shipped_at) {
                            return 'shipped';
                        }

                        return 'pending';
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'shipped',
                        'success' => 'delivered',
                    ]),
                TextColumn::make('shipped_at')
                    ->label(__('Shipped at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_delivery')
                    ->label(__('Estimated delivery'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivered_at')
                    ->label(__('Delivered at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weight')
                    ->label(__('Weight (kg)'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cost')
                    ->label(__('Cost'))
                    ->formatStateUsing(
                        static fn (mixed $state): string => $state === null
                            ? '-'
                            : Number::currency((float) $state, 'EUR', locale: app()->getLocale())
                    )
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('order_id')
                    ->label(__('Order'))
                    ->relationship(
                        name: 'order',
                        titleAttribute: 'number',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                    )
                    ->getOptionLabelFromRecordUsing(
                        static fn (Order $record): string => $record->number ?? __('Order #:id', ['id' => $record->getKey()]),
                    )
                    ->getOptionLabelUsing(
                        static fn (int|string|null $value): ?string => match (true) {
                            $value === null => null,
                            default         => optional(
                                Order::query()
                                    ->withoutGlobalScopes()
                                    ->find($value),
                            )->number ?? __('Order #:id', ['id' => $value]),
                        },
                    )
                    ->searchable(),
                SelectFilter::make('carrier_name')
                    ->label(__('Carrier'))
                    ->options(static fn (): array => OrderShipping::query()
                        ->whereNotNull('carrier_name')
                        ->orderBy('carrier_name')
                        ->pluck('carrier_name', 'carrier_name')
                        ->filter()
                        ->toArray())
                    ->searchable(),
                Filter::make('shipped_at')
                    ->label(__('Shipped at'))
                    ->form([
                        Flatpickr::makeDateTime('shipped_from')
                            ->label(__('Shipped from')),
                        Flatpickr::makeDateTime('shipped_until')
                            ->label(__('Shipped until')),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['shipped_from'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->where('shipped_at', '>=', Carbon::parse($date)),
                            )
                            ->when(
                                $data['shipped_until'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->where('shipped_at', '<=', Carbon::parse($date)),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_shipped')
                        ->label(__('Mark as shipped'))
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(static function (Collection $records): void {
                            /** @var Collection<int, OrderShipping> $records */
                            $records->each(static function (OrderShipping $shipping): void {
                                $shipping->forceFill([
                                    'shipped_at' => $shipping->shipped_at ?? now(),
                                ])->save();
                            });
                        }),
                    BulkAction::make('mark_delivered')
                        ->label(__('Mark as delivered'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(static function (Collection $records): void {
                            /** @var Collection<int, OrderShipping> $records */
                            $records->each(static function (OrderShipping $shipping): void {
                                $shipping->forceFill([
                                    'delivered_at' => $shipping->delivered_at ?? now(),
                                ])->save();
                            });
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
