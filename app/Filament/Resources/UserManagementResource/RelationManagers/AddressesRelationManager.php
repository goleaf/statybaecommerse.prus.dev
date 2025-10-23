<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Enums\AddressType;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

final class AddressesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('first_name')
                    ->label(__('addresses.first_name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line_1'),
                Forms\Components\TextInput::make('address_line_2'),
                Forms\Components\TextInput::make('city'),
                Forms\Components\TextInput::make('state'),
                Forms\Components\TextInput::make('postal_code'),
                Forms\Components\TextInput::make('country'),
                Forms\Components\Select::make('type')
                    ->options([
                        'billing'  => 'Billing',
                        'shipping' => 'Shipping',
                    ])
                    ->required(),
            ]),
            Grid::make(2)->schema([
                TextInput::make('address_line_1')
                    ->label(__('addresses.address_line_1'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('address_line_2')
                    ->label(__('addresses.address_line_2'))
                    ->maxLength(255),
                TextInput::make('city')
                    ->label(__('addresses.city'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('state')
                    ->label(__('addresses.state'))
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->label(__('addresses.postal_code'))
                    ->maxLength(20),
                Select::make('country_id')
                    ->label(__('addresses.country'))
                    ->relationship('countryById', 'name')
                    ->searchable()
                    ->preload(),
            ]),
            Grid::make(2)->schema([
                Toggle::make('is_default')
                    ->label(__('addresses.is_default'))
                    ->default(false),
                Toggle::make('is_billing')
                    ->label(__('addresses.is_billing')),
                Toggle::make('is_shipping')
                    ->label(__('addresses.is_shipping')),
                Toggle::make('is_active')
                    ->label(__('addresses.is_active')),
            ]),
            Textarea::make('notes')
                ->label(__('addresses.notes'))
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('addresses.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address_line_1')
                    ->label(__('addresses.address_line_1'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->label(__('addresses.city'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('countryById.name')
                    ->label(__('addresses.country'))
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('addresses.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'billing'  => 'info',
                        'shipping' => 'success',
                        default    => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('addresses.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('addresses.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('addresses.type'))
                    ->options(AddressType::options()),
                TernaryFilter::make('is_default')
                    ->label(__('addresses.is_default')),
                TernaryFilter::make('is_active')
                    ->label(__('addresses.is_active')),
                TrashedFilter::make(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
