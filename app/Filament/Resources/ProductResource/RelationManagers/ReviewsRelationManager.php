<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Review;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Tables;

final class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make('Review Information')
                    ->components([
                        Forms\Components\TextInput::make('reviewer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('reviewer_email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('rating')
                            ->options([
                                1 => '1 Star',
                                2 => '2 Stars',
                                3 => '3 Stars',
                                4 => '4 Stars',
                                5 => '5 Stars',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Review Content')
                    ->components([
                        Forms\Components\Textarea::make('content')
                            ->required()
                            ->rows(4),
                        Forms\Components\Toggle::make('is_approved')
                            ->label('Approved')
                            ->default(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('reviewer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn($state) => $state . ' ★'),
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->limit(100)
                    ->wrap(),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')
                    ->query(fn($query) => $query->where('is_approved', true)),
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
            ])
            ->headerActions([
                Actions\CreateAction::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn(Review $record) => $record->update(['is_approved' => true]))
                    ->visible(fn(Review $record) => !$record->is_approved),
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn(Review $record) => $record->update(['is_approved' => false]))
                    ->visible(fn(Review $record) => $record->is_approved),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_approved' => true])),
                    BulkAction::make('reject')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn($records) => $records->each->update(['is_approved' => false])),
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
