<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('address_id')
                    ->label(__('customers.address'))
                    ->relationship('address', 'street')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('street')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('type')
                            ->options([
                                'billing' => __('addresses.types.billing'),
                                'shipping' => __('addresses.types.shipping'),
                                'both' => __('addresses.types.both'),
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_default')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('address.street')
            ->columns([
                Tables\Columns\TextColumn::make('address.street')
                    ->label(__('customers.street'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('address.city')
                    ->label(__('customers.city'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('address.postal_code')
                    ->label(__('customers.postal_code'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('address.country')
                    ->label(__('customers.country'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('address.type')
                    ->label(__('customers.type'))
                    ->formatStateUsing(fn (string $state): string => __("addresses.types.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'billing' => 'primary',
                        'shipping' => 'info',
                        'both' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('address.is_default')
                    ->label(__('customers.is_default'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('address.is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('customers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('address')
                    ->label(__('customers.address'))
                    ->relationship('address', 'street')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->label(__('customers.type'))
                    ->options([
                        'billing' => __('addresses.types.billing'),
                        'shipping' => __('addresses.types.shipping'),
                        'both' => __('addresses.types.both'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('customers.is_default'))
                    ->boolean()
                    ->trueLabel(__('customers.default_only'))
                    ->falseLabel(__('customers.non_default_only'))
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('customers.is_active'))
                    ->boolean()
                    ->trueLabel(__('customers.active_only'))
                    ->falseLabel(__('customers.inactive_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
