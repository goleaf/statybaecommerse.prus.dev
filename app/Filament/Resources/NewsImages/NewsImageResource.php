<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsImages;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Models\News;
use App\Models\NewsImage;
use App\Support\Storage\SecureStorage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Schemas\Schema;

class NewsImageResource extends Resource
{
    use HasNav;

    protected static ?string $model = NewsImage::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-photo';
    }

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('news_id')
                            ->label('News Item')
                            ->relationship('news', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select the news article this image belongs to.'),
                        Toggle::make('is_featured')
                            ->label('Featured Image')
                            ->default(false)
                            ->helperText('Mark this image as featured to highlight it in listings.'),
                        FileUpload::make('file_path')
                            ->label('Image File')
                            ->image()
                            ->directory('news-images')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Upload a JPG, PNG, GIF, or WEBP file. Stored privately.'),
                        TextInput::make('alt_text')
                            ->label('Alt Text')
                            ->maxLength(255)
                            ->helperText('Describe the image for accessibility (max 255 characters).'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('Controls display order; must be a number zero or greater.'),
                        Textarea::make('caption')
                            ->label('Caption')
                            ->rows(3)
                            ->columnSpanFull()
                            ->maxLength(500)
                            ->helperText('Optional caption shown with the image (max 500 characters).'),
                        TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Optional: populate with the file size in bytes if known.'),
                        TextInput::make('mime_type')
                            ->label('MIME Type')
                            ->maxLength(255)
                            ->helperText('Optional MIME type value (e.g., image/jpeg).'),
                        Textarea::make('dimensions')
                            ->label('Dimensions')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Optional JSON object storing width/height, e.g., {"width":800,"height":600}.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label('Image')
                    ->size(80)
                    ->square()
                    ->grow(false)
                    ->defaultImageUrl(url('images/placeholder-image.svg')),
                TextColumn::make('news.title')
                    ->label('News Title')
                    ->state(fn (NewsImage $record): string => (string) ($record->news->title ?? '—'))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('news', fn (Builder $newsQuery): Builder => $newsQuery->where('title', 'like', "%{$search}%")))
                    ->limit(40),
                TextColumn::make('caption')
                    ->label('Caption')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('is_featured')
                    ->label('Featured')
                    ->badge()
                    ->state(fn (NewsImage $record): string => $record->is_featured ? 'Featured' : 'Standard')
                    ->color(fn (NewsImage $record): string => $record->is_featured ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),
                TextColumn::make('file_size_formatted')
                    ->label('File Size')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('file_size', $direction))
                    ->toggleable(),
                TextColumn::make('mime_type')
                    ->label('MIME Type')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('dimensions_display')
                    ->label('Dimensions')
                    ->state(function (NewsImage $record): string {
                        $width = $record->dimensions['width'] ?? null;
                        $height = $record->dimensions['height'] ?? null;

                        return ($width && $height) ? sprintf('%dx%d', $width, $height) : '—';
                    })
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label('News')
                    ->options(fn (): array => News::query()
                        ->where('is_visible', true)
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())
                        ->orderByDesc('published_at')
                        ->get()
                        ->mapWithKeys(fn (News $news): array => [
                            $news->id => (string) ($news->title !== '' ? $news->title : $news->author_name ?? 'News #' . $news->id),
                        ])
                        ->all())
                    ->searchable(),
                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->nullable(),
                SelectFilter::make('mime_type')
                    ->label('MIME Type')
                    ->multiple()
                    ->options(fn (): array => NewsImage::query()
                        ->whereNotNull('mime_type')
                        ->distinct()
                        ->orderBy('mime_type')
                        ->pluck('mime_type', 'mime_type')
                        ->all()),
                Filter::make('large_files')
                    ->label('Large Files')
                    ->query(fn (Builder $query): Builder => $query->where('file_size', '>=', 1024 * 1024))
                    ->indicator('Large Files'),
                Filter::make('recent_uploads')
                    ->label('Recent Uploads')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->indicator('Recent Uploads'),
                Filter::make('no_alt_text')
                    ->label('Missing Alt Text')
                    ->query(fn (Builder $query): Builder => $query->where(fn (Builder $builder): Builder => $builder
                        ->whereNull('alt_text')
                        ->orWhere('alt_text', '')))
                    ->indicator('Missing Alt Text'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('duplicate')
                        ->label('Duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->requiresConfirmation()
                        ->action(function (NewsImage $record): void {
                            $duplicate = $record->replicate();
                            $duplicate->sort_order = (int) NewsImage::query()
                                ->where('news_id', $record->news_id)
                                ->max('sort_order') + 1;
                            $duplicate->save();
                        }),
                    Action::make('download')
                        ->label('Download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (NewsImage $record): string => SecureStorage::temporarySignedUrl($record->file_path))
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
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsImage $record): void {
                                $record->update(['is_featured' => true]);
                            });
                        }),
                    BulkAction::make('unset_featured')
                        ->label('Unset Featured')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsImage $record): void {
                                $record->update(['is_featured' => false]);
                            });
                        }),
                    BulkAction::make('reorder')
                        ->label('Reorder by Selection')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->action(function (Collection $records): void {
                            $records->values()->each(function (NewsImage $record, int $index): void {
                                $record->update(['sort_order' => $index + 1]);
                            });
                        }),
                ]),
            ])
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultSort('sort_order')
            ->poll('30s')
            ->persistFiltersInSession()
            ->persistSearchInSession()
            ->persistSortInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNewsImages::route('/panel'),
            'create' => CreateNewsImage::route('/panel/create'),
            'edit'   => EditNewsImage::route('/panel/{record}/edit'),
        ];
    }
}
