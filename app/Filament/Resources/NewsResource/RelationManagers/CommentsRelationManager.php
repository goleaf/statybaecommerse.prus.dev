<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsResource\RelationManagers;

use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('comment_id')
                    ->label(__('news.comment'))
                    ->relationship('comment', 'content')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => __('comments.statuses.pending'),
                                'approved' => __('comments.statuses.approved'),
                                'rejected' => __('comments.statuses.rejected'),
                                'spam' => __('comments.statuses.spam'),
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_approved')
                            ->default(false),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment.content')
            ->columns([
                Tables\Columns\TextColumn::make('comment.content')
                    ->label(__('news.comment_content'))
                    ->searchable()
                    ->limit(100),

                Tables\Columns\TextColumn::make('comment.author_name')
                    ->label(__('news.author'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('comment.author_email')
                    ->label(__('news.author_email'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('comment.status')
                    ->label(__('news.status'))
                    ->formatStateUsing(fn (string $state): string => __("comments.statuses.{$state}"))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'spam' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('comment.is_approved')
                    ->label(__('news.is_approved'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('news.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('comment.created_at')
                    ->label(__('news.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('comment')
                    ->label(__('news.comment'))
                    ->relationship('comment', 'content')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label(__('news.status'))
                    ->options([
                        'pending' => __('comments.statuses.pending'),
                        'approved' => __('comments.statuses.approved'),
                        'rejected' => __('comments.statuses.rejected'),
                        'spam' => __('comments.statuses.spam'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('news.is_approved'))
                    ->boolean()
                    ->trueLabel(__('news.approved_only'))
                    ->falseLabel(__('news.pending_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}

