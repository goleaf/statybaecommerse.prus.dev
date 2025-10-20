<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages;

use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Models\News;
use App\Models\NewsImage;
use App\Support\Storage\SecureStorage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NewsImageResource extends Resource
{
    protected static ?string $model = NewsImage::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-photo';
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Image')
                    ->square()
                    ->size(64)
                    ->defaultImageUrl(url('/images/placeholder-image.png'))
                    ->getStateUsing(fn (NewsImage $record): ?string => $record->file_path
                        ? SecureStorage::temporarySignedUrl($record->file_path)
                        : null),
                TextColumn::make('news.title')
                    ->label('News')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                TextColumn::make('caption')
                    ->label('Caption')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('—'),
                BadgeColumn::make('is_featured')
                    ->label('Featured')
                    ->formatStateUsing(fn (?bool $state): string => $state ? 'Yes' : 'No')
                    ->colors([
                        'success' => true,
                        'gray'    => false,
                    ])
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('file_size')
                    ->label('File Size')
                    ->formatStateUsing(fn (?int $state): ?string => $state
                        ? number_format($state / 1024, 2) . ' KB'
                        : null)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('mime_type')
                    ->label('MIME Type')
                    ->colors([
                        'success' => 'image/jpeg',
                        'info'    => 'image/png',
                        'warning' => 'image/gif',
                        'primary' => 'image/webp',
                    ])
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->formatStateUsing(fn ($state): ?string => isset($state['width'], $state['height'])
                        ? $state['width'] . '×' . $state['height']
                        : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label('News')
                    ->options(fn () => News::query()->pluck('title', 'id')->all())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                SelectFilter::make('mime_type')
                    ->label('MIME Type')
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png'  => 'PNG',
                        'image/gif'  => 'GIF',
                        'image/webp' => 'WebP',
                    ])
                    ->multiple(),
                Filter::make('large_files')
                    ->label('Large Files')
                    ->query(fn (Builder $query): Builder => $query->where('file_size', '>', 1024 * 1024))
                    ->toggle(),
                Filter::make('recent_uploads')
                    ->label('Recent Uploads')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->toggle(),
                Filter::make('no_alt_text')
                    ->label('Missing Alt Text')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(fn (Builder $subQuery) => $subQuery
                            ->whereNull('alt_text')
                            ->orWhere('alt_text', '')))
                    ->toggle(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (NewsImage $record): void {
                            $newRecord = $record->replicate();
                            $newRecord->sort_order = (int) ($record->news?->images()->max('sort_order') ?? 0) + 1;
                            $newRecord->save();
                        })
                        ->requiresConfirmation(),
                    Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (NewsImage $record): ?string => $record->file_path
                            ? SecureStorage::temporarySignedUrl($record->file_path)
                            : null)
                        ->openUrlInNewTab(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('set_featured')
                        ->label('Set Featured')
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unset_featured')
                        ->label('Unset Featured')
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('reorder')
                        ->label('Reorder')
                        ->icon('heroicon-o-arrows-up-down')
                        ->action(function (Collection $records): void {
                            $records->values()->each(function (NewsImage $record, int $index): void {
                                $record->update(['sort_order' => $index + 1]);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->poll('30s')
            ->paginated([10, 25, 50, 100])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNewsImages::route('/'),
            'create' => CreateNewsImage::route('/create'),
            // 'view' page does not exist; removing mapping to avoid errors
            'edit' => EditNewsImage::route('/{record}/edit'),
        ];
    }
}
