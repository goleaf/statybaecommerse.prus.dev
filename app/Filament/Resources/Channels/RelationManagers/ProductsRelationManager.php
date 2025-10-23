<?php

declare(strict_types=1);

namespace App\Filament\Resources\Channels\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\AttachAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ProductsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        // Configure the Filament resource form schema using the v4 Schema API.
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        // Configure the Filament table definition for the resource.
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->filters([
                //
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
                CreateAction::make(),
                AttachAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}