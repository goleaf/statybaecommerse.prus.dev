<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Review;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
final class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';
    protected static ?string $title = 'Product Reviews';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->label(__('admin.reviews.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->label(__('admin.reviews.fields.rating'))
                    ->numeric(),
                    ->minValue(1)
                    ->maxValue(5)
                Forms\Components\Textarea::make('title')
                    ->label(__('admin.reviews.fields.title'))
                    ->maxLength(255),
                    ->rows(2),
                Forms\Components\Textarea::make('comment')
                    ->label(__('admin.reviews.fields.comment'))
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_approved')
                    ->label(__('admin.reviews.fields.is_approved'))
                    ->default(false),
                Forms\Components\Toggle::make('is_featured')
                    ->label(__('admin.reviews.fields.is_featured'))
                Forms\Components\KeyValue::make('metadata')
                    ->label(__('admin.reviews.fields.metadata'))
                    ->keyLabel(__('admin.reviews.fields.metadata_key'))
                    ->valueLabel(__('admin.reviews.fields.metadata_value'))
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable(),
                    ->badge(),
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->limit(50),
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                Tables\Columns\TextColumn::make('comment')
                    ->limit(100)
                        if (strlen($state) <= 100) {
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.reviews.fields.created_at'))
                    ->dateTime(),
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 ⭐',
                        2 => '2 ⭐⭐',
                        3 => '3 ⭐⭐⭐',
                        4 => '4 ⭐⭐⭐⭐',
                        5 => '5 ⭐⭐⭐⭐⭐',
                    ]),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label(__('admin.reviews.fields.is_approved')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('admin.reviews.fields.is_featured')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.reviews.filters.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.reviews.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label(__('admin.reviews.actions.approve'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_approved' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('disapprove')
                        ->label(__('admin.reviews.actions.disapprove'))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn($records) => $records->each->update(['is_approved' => false]))
                ]),
            ->defaultSort("created_at", "desc");
    }
}
}
