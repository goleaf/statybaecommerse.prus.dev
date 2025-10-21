<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Schemas\Schema;
class CartItemsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'cartItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Keep the identifier locked because the relationship is read-only in this manager.
                TextInput::make('id')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
