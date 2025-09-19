<?php declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';
    protected static ?string $title = 'admin.sections.addresses';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('first_name')
                    ->required()\n                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()\n                    ->maxLength(255),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line_1')
                    ->required()\n                    ->maxLength(255),
                Forms\Components\TextInput::make('address_line_2')
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')
                    ->required()\n                    ->maxLength(100),
                Forms\Components\TextInput::make('state')
                    ->maxLength(100),
                Forms\Components\TextInput::make('postal_code')
                    ->required()\n                    ->maxLength(20),
                Forms\Components\Select::make('country')
                    ->options([
                        'LT' => 'Lithuania',
                        'LV' => 'Latvia',
                        'EE' => 'Estonia',
                        'PL' => 'Poland',
                        'DE' => 'Germany',
                        'FR' => 'France',
                        'GB' => 'United Kingdom',
                        'US' => 'United States',
                    ])
                    ->required(),
                    ->searchable(),
                Forms\Components\Select::make('type')
                    ->options([
                        'billing' => 'Billing',
                        'shipping' => 'Shipping',
                        'both' => 'Both',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_default')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_address')
            ->columns([
                Tables\Columns\TextColumn::make('full_address')
                    ->label(__('admin.fields.full_address'))
                    ->searchable()\n                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('admin.fields.type'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'billing' => 'warning',
                        'shipping' => 'info',
                        'both' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('admin.fields.is_default'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'billing' => 'Billing',
                        'shipping' => 'Shipping',
                        'both' => 'Both',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
