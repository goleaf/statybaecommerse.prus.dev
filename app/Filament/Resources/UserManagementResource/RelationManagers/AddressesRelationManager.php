<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Enums\AddressType;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class AddressesRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Addresses';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaGrid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label(__('addresses.fields.first_name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->label(__('addresses.fields.last_name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_name')
                        ->label(__('addresses.fields.company_name'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('company_vat')
                        ->label(__('addresses.fields.company_vat'))
                        ->maxLength(50),
                    TextInput::make('address_line_1')
                        ->label(__('addresses.fields.address_line_1'))
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('address_line_2')
                        ->label(__('addresses.fields.address_line_2'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('city')
                        ->label(__('addresses.fields.city'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('state')
                        ->label(__('addresses.fields.state'))
                        ->maxLength(255),
                    TextInput::make('postal_code')
                        ->label(__('addresses.fields.postal_code'))
                        ->required()
                        ->maxLength(20),
                    Select::make('country_id')
                        ->label(__('addresses.fields.country'))
                        ->relationship('countryById', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('phone')
                        ->label(__('addresses.fields.phone'))
                        ->tel()
                        ->maxLength(20),
                    TextInput::make('email')
                        ->label(__('addresses.fields.email'))
                        ->email()
                        ->maxLength(255),
                ])
                ->columns(2),
            SchemaGrid::make(2)
                ->schema([
                    Select::make('type')
                        ->label(__('addresses.fields.type'))
                        ->options(AddressType::options())
                        ->required()
                        ->default(AddressType::SHIPPING->value),
                    Toggle::make('is_default')
                        ->label(__('addresses.fields.is_default'))
                        ->default(false),
                    Toggle::make('is_billing')
                        ->label(__('addresses.fields.is_billing')),
                    Toggle::make('is_shipping')
                        ->label(__('addresses.fields.is_shipping')),
                    Toggle::make('is_active')
                        ->label(__('addresses.fields.is_active')),
                ])
                ->columns(2),
            Textarea::make('notes')
                ->label(__('addresses.fields.notes'))
                ->maxLength(1000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
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
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
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
