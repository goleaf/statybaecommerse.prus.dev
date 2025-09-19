<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsImageResource\Pages;
use App\Models\News;
use App\Models\NewsImage;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use UnitEnum;

final class NewsImageResource extends Resource
{
    protected static ?string $model = NewsImage::class;
    
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-photo';
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;
    
    protected static ?int $navigationSort = 4;
    
    public static function getNavigationLabel(): string
    {
        return __('admin.news_images.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.news_images.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.news_images.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.news_images.basic_information'))
                    ->description(__('admin.news_images.basic_information_description'))
                    ->schema([
                        Select::make('news_id')
                            ->label(__('admin.news_images.news'))
                            ->options(News::pluck('title', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        FileUpload::make('file_path')
                            ->label(__('admin.news_images.file_path'))
                            ->required()
                            ->image()
                            ->directory('news-images')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(5120) // 5MB
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('alt_text')
                                    ->label(__('admin.news_images.alt_text'))
                                    ->maxLength(255),

                                TextInput::make('sort_order')
                                    ->label(__('admin.news_images.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),

                        Textarea::make('caption')
                            ->label(__('admin.news_images.caption'))
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_featured')
                                    ->label(__('admin.news_images.is_featured'))
                                    ->default(false),

                                TextInput::make('file_size')
                                    ->label(__('admin.news_images.file_size'))
                                    ->numeric()
                                    ->disabled(),

                                TextInput::make('mime_type')
                                    ->label(__('admin.news_images.mime_type'))
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('file_path')
                    ->label(__('admin.news_images.image'))
                    ->square()
                    ->size(60),

                TextColumn::make('news.title')
                    ->label(__('admin.news_images.news'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('alt_text')
                    ->label(__('admin.news_images.alt_text'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('caption')
                    ->label(__('admin.news_images.caption'))
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                IconColumn::make('is_featured')
                    ->label(__('admin.news_images.is_featured'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('admin.news_images.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('file_size')
                    ->label(__('admin.news_images.file_size'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : '')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mime_type')
                    ->label(__('admin.news_images.mime_type'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label(__('admin.news_images.news'))
                    ->options(News::pluck('title', 'id'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_featured')
                    ->label(__('admin.news_images.is_featured'))
                    ->boolean(),

                SelectFilter::make('mime_type')
                    ->label(__('admin.news_images.mime_type'))
                    ->options([
                        'image/jpeg' => 'JPEG',
                        'image/png' => 'PNG',
                        'image/gif' => 'GIF',
                        'image/webp' => 'WebP',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('set_featured')
                        ->label(__('admin.news_images.set_featured'))
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each->update(['is_featured' => true]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unset_featured')
                        ->label(__('admin.news_images.unset_featured'))
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each->update(['is_featured' => false]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListNewsImages::route('/'),
            'create' => Pages\CreateNewsImage::route('/create'),
            'view' => Pages\ViewNewsImage::route('/{record}'),
            'edit' => Pages\EditNewsImage::route('/{record}/edit'),
        ];
    }
}
