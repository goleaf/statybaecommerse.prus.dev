<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * OrdersRelationManager
 * 
 * Filament resource for admin panel management.
 */
class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'admin.customers.orders';

    protected static ?string $modelLabel = 'admin.orders.order';

    protected static ?string $pluralModelLabel = 'admin.orders.orders';

    public function form(Form $form): Form
    {
        return $schema->schema([
                Forms\Components\TextInput::make('number')
                    ->label(__('admin.orders.fields.number'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->options([
                        'pending' => __('admin.orders.status.pending'),
                        'confirmed' => __('admin.orders.status.confirmed'),
                        'processing' => __('admin.orders.status.processing'),
                        'shipped' => __('admin.orders.status.shipped'),
                        'delivered' => __('admin.orders.status.delivered'),
                        'cancelled' => __('admin.orders.status.cancelled'),
                        'refunded' => __('admin.orders.status.refunded'),
                        'completed' => __('admin.orders.status.completed'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('total')
                    ->label(__('admin.orders.fields.total'))
                    ->numeric()
                    ->prefix('€')
                    ->required(),

                Forms\Components\Select::make('payment_status')
                    ->label(__('admin.orders.fields.payment_status'))
                    ->options([
                        'pending' => __('admin.orders.payment_status.pending'),
                        'paid' => __('admin.orders.payment_status.paid'),
                        'failed' => __('admin.orders.payment_status.failed'),
                        'refunded' => __('admin.orders.payment_status.refunded'),
                        'partially_refunded' => __('admin.orders.payment_status.partially_refunded'),
                        'cancelled' => __('admin.orders.payment_status.cancelled'),
                    ])
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->label(__('admin.orders.fields.notes'))
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('admin.orders.fields.number'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'primary' => 'processing',
                        'success' => ['shipped', 'delivered', 'completed'],
                        'danger' => ['cancelled', 'refunded'],
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.orders.status.{$state}")),

                Tables\Columns\TextColumn::make('total')
                    ->label(__('admin.orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label(__('admin.orders.fields.payment_status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => ['failed', 'cancelled'],
                        'info' => ['refunded', 'partially_refunded'],
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.orders.payment_status.{$state}")),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.orders.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.orders.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.orders.fields.status'))
                    ->options([
                        'pending' => __('admin.orders.status.pending'),
                        'confirmed' => __('admin.orders.status.confirmed'),
                        'processing' => __('admin.orders.status.processing'),
                        'shipped' => __('admin.orders.status.shipped'),
                        'delivered' => __('admin.orders.status.delivered'),
                        'cancelled' => __('admin.orders.status.cancelled'),
                        'refunded' => __('admin.orders.status.refunded'),
                        'completed' => __('admin.orders.status.completed'),
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label(__('admin.orders.fields.payment_status'))
                    ->options([
                        'pending' => __('admin.orders.payment_status.pending'),
                        'paid' => __('admin.orders.payment_status.paid'),
                        'failed' => __('admin.orders.payment_status.failed'),
                        'refunded' => __('admin.orders.payment_status.refunded'),
                        'partially_refunded' => __('admin.orders.payment_status.partially_refunded'),
                        'cancelled' => __('admin.orders.payment_status.cancelled'),
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->label(__('admin.orders.fields.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.customers.filters.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.customers.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.orders.create')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.actions.edit')),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
