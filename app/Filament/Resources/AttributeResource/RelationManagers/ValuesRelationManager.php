<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use App\Models\AttributeValue;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $title = 'Attribute Values';

    public function form(Schema $formSchema): Schema
    {
        return $formSchema
            ->schema([
                Forms\Components\TextInput::make('value')
                    ->label(__('translations.value'))
                    ->required(),
                    ->maxLength(255),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric(),
                    ->default(0),
                Forms\Components\Toggle::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label(__('translations.value'))
                    ->searchable(),
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric(),
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('translations.enabled'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime(),
                    ->sortable(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort("created_at", "desc");
    }
}
    }
}
