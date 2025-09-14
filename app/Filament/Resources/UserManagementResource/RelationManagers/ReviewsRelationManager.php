<?php declare(strict_types=1);

namespace App\Filament\Resources\UserManagementResource\RelationManagers;
use App\Models\Review;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
final class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';
    protected static ?string $title = 'Reviews';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                    ->searchable(),
                    ->preload(),
                Forms\Components\TextInput::make('rating')
                    ->numeric(),
                    ->minValue(1)
                    ->maxValue(5)
                    ->required(),
                Forms\Components\Textarea::make('title')
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->maxLength(1000),
                Forms\Components\Toggle::make('is_approved')
                    ->default(false),
                Forms\Components\Toggle::make('is_featured')
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->badge(),
                    ->color(fn (int $state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->limit(50),
                Tables\Columns\TextColumn::make('content')
                    ->limit(100),
                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->options([
                        1 => '1 Star',
                        2 => '2 Stars',
                        3 => '3 Stars',
                        4 => '4 Stars',
                        5 => '5 Stars',
                    ]),
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Approved'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TrashedFilter::make(),
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_approved' => true])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('disapprove')
                        ->label('Disapprove')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_approved' => false])))
                ]),
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
}
