<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderShipping;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'shipping';

    protected static ?string $recordTitleAttribute = 'tracking_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.enum_values.types.shipping_status');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('shipping_method')
                    ->required()
                    ->maxLength(255),
                TextInput::make('carrier')
                    ->maxLength(255),
                TextInput::make('carrier_name')
                    ->maxLength(255),
                TextInput::make('service')
                    ->maxLength(255),
                TextInput::make('service_type')
                    ->maxLength(255),
                TextInput::make('tracking_number')
                    ->maxLength(255),
                TextInput::make('tracking_url')
                    ->url()
                    ->maxLength(2048),
                TextInput::make('base_cost')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('insurance_cost')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('total_cost')
                    ->numeric()
                    ->prefix('€'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('estimated_delivery'),
                DateTimePicker::make('delivered_at'),
                Textarea::make('delivery_notes')
                    ->maxLength(1000)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->maxLength(2000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $carrier = trim((string) ($data['carrier'] ?? ''));
                        $carrierName = trim((string) ($data['carrier_name'] ?? ''));

                        $data['carrier'] = $carrier !== '' ? $carrier : $carrierName;
                        $data['carrier_name'] = $carrierName !== '' ? $carrierName : $carrier;
                        $data['status'] = (string) ($data['status'] ?? 'pending');
                        $data['is_delivered'] = (bool) ($data['is_delivered'] ?? false);

                        return $data;
                    }),
            ])
            ->columns([
                TextColumn::make('shipping_method')
                    ->label(__('messages.shipping_method'))
                    ->placeholder('-')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('messages.status'))
                    ->formatStateUsing(function (?string $state): string {
                        $normalizedState = strtolower((string) $state);
                        $translationKey = 'messages.shipping_statuses.' . $normalizedState;
                        $translatedState = __($translationKey);

                        return $translatedState !== $translationKey
                            ? $translatedState
                            : Str::headline(str_replace('_', ' ', $normalizedState));
                    })
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'info'    => ['shipped', 'in_transit'],
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('carrier_name')
                    ->label(__('messages.carrier'))
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('tracking_number')
                    ->label(__('messages.tracking_number'))
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shipped_at')
                    ->label(__('messages.shipped_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->label(__('messages.delivered_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('shipping_method')
                    ->options([
                        'standard'      => 'Standard',
                        'express'       => 'Express',
                        'overnight'     => 'Overnight',
                        'pickup'        => 'Pickup',
                        'international' => 'International',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('mark_shipped')
                    ->label(__('messages.shipped'))
                    ->icon('heroicon-m-truck')
                    ->color('info')
                    ->visible(fn (OrderShipping $record): bool => blank($record->shipped_at) && blank($record->delivered_at))
                    ->action(function (OrderShipping $record): void {
                        $record->forceFill([
                            'status'       => 'shipped',
                            'shipped_at'   => now(),
                            'is_delivered' => false,
                        ])->save();

                        Notification::make()
                            ->title(__('messages.shipped'))
                            ->success()
                            ->send();
                    }),
                Action::make('mark_delivered')
                    ->label(__('messages.delivered'))
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (OrderShipping $record): bool => blank($record->delivered_at))
                    ->action(function (OrderShipping $record): void {
                        $record->forceFill([
                            'status'       => 'delivered',
                            'shipped_at'   => $record->shipped_at ?? now(),
                            'delivered_at' => now(),
                            'is_delivered' => true,
                        ])->save();

                        Notification::make()
                            ->title(__('messages.delivered'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_shipped')
                        ->label(__('messages.shipped'))
                        ->icon('heroicon-m-truck')
                        ->action(function (Collection $records): void {
                            $records->each(function (OrderShipping $record): void {
                                $record->forceFill([
                                    'status'       => 'shipped',
                                    'shipped_at'   => now(),
                                    'is_delivered' => false,
                                ])->save();
                            });

                            Notification::make()
                                ->title(__('messages.shipped'))
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
