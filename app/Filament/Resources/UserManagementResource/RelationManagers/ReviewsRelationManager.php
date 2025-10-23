<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Zvizvi\RelationManagerRepeater\Tables\RelationManagerRepeaterAction;

final class ReviewsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Reviews';

    public function form(Schema $schema): Schema
    {
        return $form->schema([
            Select::make('product_id')
                ->relationship('product', 'name')
                ->label(__('products.fields.name'))
                ->required()
                ->searchable()
                ->preload(),
            TextInput::make('rating')
                ->label(__('reviews.fields.rating'))
                ->numeric()
                ->minValue(1)
                ->maxValue(5)
                ->required(),
            Textarea::make('title')
                ->label(__('reviews.fields.title'))
                ->maxLength(255),
            Textarea::make('content')
                ->label(__('reviews.fields.content'))
                ->maxLength(1000)
                ->columnSpanFull(),
            Toggle::make('is_approved')
                ->label(__('reviews.fields.is_approved'))
                ->default(false),
            Toggle::make('is_featured')
                ->label(__('reviews.fields.is_featured')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('products.fields.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('rating')
                    ->label(__('reviews.fields.rating'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default     => 'danger',
                    }),
                TextColumn::make('title')
                    ->label(__('reviews.fields.title'))
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('content')
                    ->label(__('reviews.fields.content'))
                    ->limit(100),
                IconColumn::make('is_approved')
                    ->label(__('reviews.fields.is_approved'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('reviews.fields.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('reviews.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label(__('reviews.fields.rating'))
                    ->options([
                        1 => '1 ★',
                        2 => '2 ★',
                        3 => '3 ★',
                        4 => '4 ★',
                        5 => '5 ★',
                    ]),
                TernaryFilter::make('is_approved')
                    ->label(__('reviews.fields.is_approved')),
                TernaryFilter::make('is_featured')
                    ->label(__('reviews.fields.is_featured')),
                TrashedFilter::make(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('reviews.actions.approve'))
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_approved' => true])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('disapprove')
                        ->label(__('reviews.actions.disapprove'))
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_approved' => false]))),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
                ActiveScope::class,
                ApprovedScope::class,
            ]));
    }
}
