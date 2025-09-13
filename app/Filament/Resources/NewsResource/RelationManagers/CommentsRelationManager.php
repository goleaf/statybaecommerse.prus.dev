<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('author_name')
                    ->label(__('admin.news.comments.fields.author_name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author_email')
                    ->label(__('admin.news.comments.fields.author_email'))
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->label(__('admin.news.comments.fields.content'))
                    ->required(),
                Forms\Components\Toggle::make('is_approved')
                    ->label(__('admin.news.comments.fields.is_approved'))
                    ->default(false),
                Forms\Components\Toggle::make('is_visible')
                    ->label(__('admin.news.comments.fields.is_visible'))
                    ->default(true),
                Forms\Components\Select::make('parent_id')
                    ->label(__('admin.news.comments.fields.parent_id'))
                    ->relationship('parent', 'content')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->columns([
                Tables\Columns\TextColumn::make('author_name')
                    ->label(__('admin.news.comments.fields.author_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author_email')
                    ->label(__('admin.news.comments.fields.author_email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('admin.news.comments.fields.content'))
                    ->limit(100)
                    ->html(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('admin.news.comments.fields.is_approved'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.news.comments.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('parent.content')
                    ->label(__('admin.news.comments.fields.parent_id'))
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('admin.news.comments.filters.is_approved')),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('admin.news.comments.filters.is_visible')),
                Tables\Filters\Filter::make('has_replies')
                    ->label(__('admin.news.comments.filters.has_replies'))
                    ->query(fn (Builder $query): Builder => $query->whereHas('replies')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label(__('admin.news.comments.approve'))
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        $record->update(['is_approved' => true]);
                    })
                    ->visible(fn ($record): bool => ! $record->is_approved),
                Tables\Actions\Action::make('reject')
                    ->label(__('admin.news.comments.reject'))
                    ->icon('heroicon-o-x-mark')
                    ->action(function ($record) {
                        $record->update(['is_approved' => false]);
                    })
                    ->visible(fn ($record): bool => $record->is_approved),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('admin.news.comments.approve'))
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => true]);
                        }),
                    Tables\Actions\BulkAction::make('reject')
                        ->label(__('admin.news.comments.reject'))
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => false]);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
