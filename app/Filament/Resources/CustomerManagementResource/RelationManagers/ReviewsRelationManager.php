<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use App\Models\Review;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

final class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('review_id')
                    ->label(__('customers.review'))
                    ->relationship('review', 'title')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Select::make('rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_approved')
                            ->default(false),
                    ]),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('review.title')
            ->columns([
                Tables\Columns\TextColumn::make('review.title')
                    ->label(__('customers.review_title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('review.rating')
                    ->label(__('customers.rating'))
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . ' (' . $state . '/5)')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        5 => 'success',
                        4 => 'info',
                        3 => 'warning',
                        2 => 'danger',
                        1 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('review.content')
                    ->label(__('customers.content'))
                    ->searchable()
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('review.is_approved')
                    ->label(__('customers.approved'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('review.created_at')
                    ->label(__('customers.review_date'))
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('customers.sort_order'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 100 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 20 => 'info',
                        $state >= 10 => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('review')
                    ->label(__('customers.review'))
                    ->relationship('review', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('rating')
                    ->label(__('customers.rating'))
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('customers.approved'))
                    ->boolean()
                    ->trueLabel(__('customers.approved_only'))
                    ->falseLabel(__('customers.pending_only'))
                    ->native(false),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                EditAction::make(),
                Tables\Actions\DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('review.created_at', 'desc');
    }
}
