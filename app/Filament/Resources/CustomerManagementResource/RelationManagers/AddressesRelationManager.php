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
 * AddressesRelationManager
 * 
 * Filament resource for admin panel management.
 */
class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'admin.customers.addresses';

    protected static ?string $modelLabel = 'admin.customers.address';

    protected static ?string $pluralModelLabel = 'admin.customers.addresses';

    public function form(Form $form): Form
    {
        return $schema->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.customers.fields.name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('company')
                    ->label(__('admin.customers.fields.company'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('address_line_1')
                    ->label(__('admin.customers.fields.address_line_1'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('address_line_2')
                    ->label(__('admin.customers.fields.address_line_2'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('city')
                    ->label(__('admin.customers.fields.city'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('state')
                    ->label(__('admin.customers.fields.state'))
                    ->maxLength(255),

                Forms\Components\TextInput::make('postal_code')
                    ->label(__('admin.customers.fields.postal_code'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('country')
                    ->label(__('admin.customers.fields.country'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->label(__('admin.customers.fields.phone'))
                    ->tel()
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_default')
                    ->label(__('admin.customers.fields.is_default'))
                    ->helperText(__('admin.is_default_help')),

                Forms\Components\Toggle::make('is_billing')
                    ->label(__('admin.customers.fields.is_billing'))
                    ->helperText(__('admin.is_billing_help')),

                Forms\Components\Toggle::make('is_shipping')
                    ->label(__('admin.customers.fields.is_shipping'))
                    ->helperText(__('admin.is_shipping_help')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.customers.fields.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('company')
                    ->label(__('admin.customers.fields.company'))
                    ->searchable()
                    ->placeholder(__('admin.no_company')),

                Tables\Columns\TextColumn::make('address_line_1')
                    ->label(__('admin.customers.fields.address_line_1'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('city')
                    ->label(__('admin.customers.fields.city'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label(__('admin.customers.fields.postal_code'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('country')
                    ->label(__('admin.customers.fields.country'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('admin.customers.fields.is_default'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_billing')
                    ->label(__('admin.customers.fields.is_billing'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('info')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_shipping')
                    ->label(__('admin.customers.fields.is_shipping'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('admin.customers.fields.is_default'))
                    ->nullable()
                    ->trueLabel(__('admin.default_addresses'))
                    ->falseLabel(__('admin.non_default_addresses')),

                Tables\Filters\TernaryFilter::make('is_billing')
                    ->label(__('admin.customers.fields.is_billing'))
                    ->nullable()
                    ->trueLabel(__('admin.billing_addresses'))
                    ->falseLabel(__('admin.non_billing_addresses')),

                Tables\Filters\TernaryFilter::make('is_shipping')
                    ->label(__('admin.customers.fields.is_shipping'))
                    ->nullable()
                    ->trueLabel(__('admin.shipping_addresses'))
                    ->falseLabel(__('admin.non_shipping_addresses')),

                Tables\Filters\Filter::make('country')
                    ->label(__('admin.customers.fields.country'))
                    ->form([
                        Forms\Components\TextInput::make('country')
                            ->label(__('admin.customers.fields.country')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['country'],
                            fn (Builder $query, $country): Builder => $query->where('country', 'like', "%{$country}%"),
                        );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.customers.create_address')),
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
            ->defaultSort('is_default', 'desc');
    }
}
