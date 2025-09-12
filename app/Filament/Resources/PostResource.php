<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Models\Post;
use App\Models\User;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use FilamentTiptapEditor\TiptapEditor;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use UnitEnum;

final class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    /**
     * @var string|\BackedEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-document-text';

    /**
     * @var string|\BackedEnum|null
     */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('posts.title');
    }

    public static function getModelLabel(): string
    {
        return __('posts.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('posts.title');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('posts.fields.title'))
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('posts.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('posts.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->rules(['alpha_dash']),
                        Forms\Components\Select::make('user_id')
                            ->label(__('posts.fields.user_id'))
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label(__('posts.fields.status'))
                            ->options([
                                'draft' => __('posts.status.draft'),
                                'published' => __('posts.status.published'),
                                'archived' => __('posts.status.archived'),
                            ])
                            ->required()
                            ->default('draft'),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('posts.fields.published_at'))
                            ->displayFormat('d/m/Y H:i')
                            ->default(now()),
                        Forms\Components\Toggle::make('featured')
                            ->label(__('posts.fields.featured'))
                            ->default(false),
                    ])
                    ->columns(2),
                Forms\Components\Section::make(__('posts.fields.content'))
                    ->schema([
                        TiptapEditor::make('content')
                            ->label(__('posts.fields.content'))
                            ->profile('default')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('excerpt')
                            ->label(__('posts.fields.excerpt'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make(__('posts.fields.images'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->collection('images')
                            ->multiple(false)
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->label(__('posts.fields.images')),
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->multiple(true)
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->label(__('posts.fields.gallery')),
                    ])
                    ->columns(1),
                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label(__('posts.fields.meta_title'))
                            ->maxLength(60)
                            ->helperText(__('posts.seo.meta_title_help')),
                        Forms\Components\Textarea::make('meta_description')
                            ->label(__('posts.fields.meta_description'))
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText(__('posts.seo.meta_description_help')),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->collection('images')
                    ->conversion('thumb')
                    ->size(60)
                    ->circular(),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('posts.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('posts.fields.user_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('posts.fields.status'))
                    ->formatStateUsing(fn(string $state): string => __('posts.status.' . $state))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),
                Tables\Columns\IconColumn::make('featured')
                    ->label(__('posts.fields.featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('posts.fields.published_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('posts.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('posts.filters.status'))
                    ->options([
                        'draft' => __('posts.status.draft'),
                        'published' => __('posts.status.published'),
                        'archived' => __('posts.status.archived'),
                    ]),
                Tables\Filters\TernaryFilter::make('featured')
                    ->label(__('posts.filters.featured'))
                    ->placeholder(__('posts.filters.all_posts'))
                    ->trueLabel(__('posts.filters.featured_only'))
                    ->falseLabel(__('posts.filters.not_featured')),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('posts.filters.author'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label(__('posts.filters.published_from')),
                        Forms\Components\DatePicker::make('published_until')
                            ->label(__('posts.filters.published_until')),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn($query, $date) => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn($query, $date) => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('publish')
                    ->label(__('posts.actions.publish'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Post $record): bool => $record->status === 'draft')
                    ->action(fn(Post $record) => $record->update(['status' => 'published', 'published_at' => now()]))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('unpublish')
                    ->label(__('posts.actions.unpublish'))
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn(Post $record): bool => $record->status === 'published')
                    ->action(fn(Post $record) => $record->update(['status' => 'draft']))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('archive')
                    ->label(__('posts.actions.archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->visible(fn(Post $record): bool => $record->status !== 'archived')
                    ->action(fn(Post $record) => $record->update(['status' => 'archived']))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('feature')
                    ->label(__('posts.actions.feature'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(Post $record): bool => !$record->featured)
                    ->action(fn(Post $record) => $record->update(['featured' => true])),
                Tables\Actions\Action::make('unfeature')
                    ->label(__('posts.actions.unfeature'))
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn(Post $record): bool => $record->featured)
                    ->action(fn(Post $record) => $record->update(['featured' => false])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn() => 'posts-' . date('Y-m-d'))
                                ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                                ->withColumns([
                                    Column::make('title')->heading('Title'),
                                    Column::make('user.name')->heading('Author'),
                                    Column::make('status')->heading('Status'),
                                    Column::make('featured')->heading('Featured'),
                                    Column::make('published_at')->heading('Published At'),
                                    Column::make('created_at')->heading('Created At'),
                                ]),
                        ]),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\PostStatsWidget::class,
            Widgets\RecentPostsWidget::class,
            Widgets\PostsByStatusWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt'];
    }
}
