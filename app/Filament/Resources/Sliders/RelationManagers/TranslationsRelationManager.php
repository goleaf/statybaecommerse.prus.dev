<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\RelationManagers;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $recordTitleAttribute = 'locale';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('messages.translations');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('locale')
                    ->label(__('messages.locale'))
                    ->options(config('filament-language-tabs.default_locales', ['lt' => 'Lithuanian', 'en' => 'English']))
                    ->required(),
                TextInput::make('title')
                    ->label(__('translations.title'))
                    ->required()
                    ->maxLength(255),
                RichEditor::make('description')
                    ->label(__('translations.description'))
                    ->maxLength(2000),
                TextInput::make('button_text')
                    ->label(__('translations.button_text'))
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('locale')
                    ->label(__('messages.locale'))
                    ->sortable(),
                TextColumn::make('title')
                    ->sortable()
                    ->label(__('translations.title'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
