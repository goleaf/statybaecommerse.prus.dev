<?php

declare(strict_types=1);

namespace App\Filament\Resources\CityResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChildrenRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Child Cities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->rows(3),
                Forms\Components\Select::make('level')
                    ->options([
                        0 => 'City',
                        1 => 'District',
                        2 => 'Neighborhood',
                        3 => 'Suburb',
                    ])
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('population')
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Toggle::make('is_enabled')
                    ->default(true),
                Forms\Components\Toggle::make('is_capital')
                    ->default(false),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'City',
                        1 => 'District',
                        2 => 'Neighborhood',
                        3 => 'Suburb',
                        default => 'City',
                    })
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'success',
                        1 => 'info',
                        2 => 'warning',
                        3 => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('population')
                    ->numeric()
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state) : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_capital')
                    ->boolean()
                    ->trueIcon('heroicon-o-crown')
                    ->trueColor('warning'),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        0 => 'City',
                        1 => 'District',
                        2 => 'Neighborhood',
                        3 => 'Suburb',
                    ]),
                Tables\Filters\TernaryFilter::make('is_enabled'),
                Tables\Filters\TernaryFilter::make('is_capital'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
