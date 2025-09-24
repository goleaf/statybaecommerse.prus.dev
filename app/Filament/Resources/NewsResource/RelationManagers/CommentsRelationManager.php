<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

final class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('author_name')
                    ->label(__('news.fields.author_name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author_email')
                    ->label(__('news.fields.author_email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->label(__('news.fields.content'))
                    ->required()
                    ->maxLength(1000)
                    ->rows(4),
                Forms\Components\Select::make('parent_id')
                    ->label(__('news.fields.parent_comment'))
                    ->relationship('parent', 'content')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Toggle::make('is_approved')
                    ->label(__('news.fields.is_approved'))
                    ->default(false),
                Forms\Components\Toggle::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label(__('news.fields.content'))
                    ->searchable()
                    ->limit(100)
                    ->wrap(),
                Tables\Columns\TextColumn::make('author_name')
                    ->label(__('news.fields.author_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('author_email')
                    ->label(__('news.fields.author_email'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('parent.content')
                    ->label(__('news.fields.parent_comment'))
                    ->limit(50)
                    ->placeholder(__('news.fields.no_parent'))
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('news.fields.is_approved'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label(__('news.fields.parent_comment'))
                    ->relationship('parent', 'content')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('news.fields.is_approved'))
                    ->boolean()
                    ->trueLabel(__('news.approved_only'))
                    ->falseLabel(__('news.pending_only'))
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean()
                    ->trueLabel(__('news.visible_only'))
                    ->falseLabel(__('news.hidden_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
