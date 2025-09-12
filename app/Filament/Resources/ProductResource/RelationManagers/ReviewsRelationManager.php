<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reviewer_name')
                    ->label(__('translations.reviewer_name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('reviewer_email')
                    ->label(__('translations.reviewer_email'))
                    ->email()
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('rating')
                    ->label(__('translations.rating'))
                    ->options([
                        1 => '1 '.__('translations.star'),
                        2 => '2 '.__('translations.stars'),
                        3 => '3 '.__('translations.stars'),
                        4 => '4 '.__('translations.stars'),
                        5 => '5 '.__('translations.stars'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label(__('translations.review_title'))
                    ->maxLength(255),

                Forms\Components\Textarea::make('content')
                    ->label(__('translations.review_content'))
                    ->required()
                    ->rows(4),

                Forms\Components\Toggle::make('is_approved')
                    ->label(__('translations.is_approved'))
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label(__('translations.reviewer_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewer_email')
                    ->label(__('translations.reviewer_email'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label(__('translations.rating'))
                    ->formatStateUsing(fn (int $state): string => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('translations.review_title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('content')
                    ->label(__('translations.review_content'))
                    ->limit(100)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 100) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label(__('translations.is_approved'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label(__('translations.rating'))
                    ->options([
                        1 => '1 '.__('translations.star'),
                        2 => '2 '.__('translations.stars'),
                        3 => '3 '.__('translations.stars'),
                        4 => '4 '.__('translations.stars'),
                        5 => '5 '.__('translations.stars'),
                    ]),

                Tables\Filters\SelectFilter::make('is_approved')
                    ->label(__('translations.is_approved'))
                    ->options([
                        true => __('translations.yes'),
                        false => __('translations.no'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label(__('translations.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(fn (Review $record) => $record->update(['is_approved' => true]))
                    ->visible(fn (Review $record) => ! $record->is_approved),

                Tables\Actions\Action::make('disapprove')
                    ->label(__('translations.disapprove'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(fn (Review $record) => $record->update(['is_approved' => false]))
                    ->visible(fn (Review $record) => $record->is_approved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('translations.approve'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true])),

                    Tables\Actions\BulkAction::make('disapprove')
                        ->label(__('translations.disapprove'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_approved' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
