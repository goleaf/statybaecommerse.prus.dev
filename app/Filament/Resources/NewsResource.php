<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\NewsCategory;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Form;

/**
 * NewsResource
 * 
 * Filament v4 resource for News management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class NewsResource extends Resource
{
    protected static ?string $model = News::class;
    
    /** @var UnitEnum|string|null */
        protected static $navigationGroup = NavigationGroup::
    
    ;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('news.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Content'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('news.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('news.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('news.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('news.title'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                    $operation === 'create' ? $set('slug', \Str::slug($state)) : null
                                ),
                            
                            TextInput::make('slug')
                                ->label(__('news.slug'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    
                    Textarea::make('excerpt')
                        ->label(__('news.excerpt'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    
                    RichEditor::make('content')
                        ->label(__('news.content'))
                        ->required()
                        ->columnSpanFull()
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'strike',
                            'link',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'blockquote',
                            'codeBlock',
                        ]),
                ]),
            
            Section::make(__('news.media'))
                ->schema([
                    FileUpload::make('featured_image')
                        ->label(__('news.featured_image'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '16:9',
                            '4:3',
                            '1:1',
                        ])
                        ->directory('news/featured')
                        ->visibility('public')
                        ->columnSpanFull(),
                    
                    FileUpload::make('gallery')
                        ->label(__('news.gallery'))
                        ->image()
                        ->multiple()
                        ->imageEditor()
                        ->directory('news/gallery')
                        ->visibility('public')
                        ->columnSpanFull(),
                ]),
            
            Section::make(__('news.categorization'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('category_id')
                                ->label(__('news.category'))
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
                            
                            TagsInput::make('tags')
                                ->label(__('news.tags'))
                                ->placeholder(__('news.add_tag'))
                                ->separator(','),
                        ]),
                ]),
            
            Section::make(__('news.publishing'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_published')
                                ->label(__('news.is_published'))
                                ->default(false),
                            
                            Toggle::make('is_featured')
                                ->label(__('news.is_featured')),
                        ]),
                    
                    Grid::make(2)
                        ->schema([
                            DateTimePicker::make('published_at')
                                ->label(__('news.published_at'))
                                ->default(now())
                                ->displayFormat('Y-m-d H:i'),
                            
                            DateTimePicker::make('expires_at')
                                ->label(__('news.expires_at'))
                                ->displayFormat('Y-m-d H:i'),
                        ]),
                ]),
            
            Section::make(__('news.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('news.seo_title'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                    
                    Textarea::make('seo_description')
                        ->label(__('news.seo_description'))
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                    
                    TextInput::make('seo_keywords')
                        ->label(__('news.seo_keywords'))
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label(__('news.featured_image'))
                    ->circular()
                    ->size(40),
                
                TextColumn::make('title')
                    ->label(__('news.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                
                TextColumn::make('category.name')
                    ->label(__('news.category'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                TextColumn::make('excerpt')
                    ->label(__('news.excerpt'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('is_published')
                    ->label(__('news.status'))
                    ->formatStateUsing(fn (bool $state): string => $state ? __('news.published') : __('news.draft'))
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ]),
                
                IconColumn::make('is_featured')
                    ->label(__('news.is_featured'))
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('published_at')
                    ->label(__('news.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('views_count')
                    ->label(__('news.views_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label(__('news.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label(__('news.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label(__('news.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                TernaryFilter::make('is_published')
                    ->label(__('news.is_published'))
                    ->boolean()
                    ->trueLabel(__('news.published_only'))
                    ->falseLabel(__('news.draft_only'))
                    ->native(false),
                
                TernaryFilter::make('is_featured')
                    ->label(__('news.is_featured'))
                    ->boolean()
                    ->trueLabel(__('news.featured_only'))
                    ->falseLabel(__('news.not_featured'))
                    ->native(false),
                
                Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label(__('news.published_from')),
                        Forms\Components\DatePicker::make('published_until')
                            ->label(__('news.published_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                
                Action::make('publish')
                    ->label(__('news.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (News $record): bool => !$record->is_published)
                    ->action(function (News $record): void {
                        $record->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title(__('news.published_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Action::make('unpublish')
                    ->label(__('news.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (News $record): bool => $record->is_published)
                    ->action(function (News $record): void {
                        $record->update(['is_published' => false]);
                        
                        Notification::make()
                            ->title(__('news.unpublished_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    
                    BulkAction::make('publish')
                        ->label(__('news.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_published' => true,
                                'published_at' => now(),
                            ]);
                            
                            Notification::make()
                                ->title(__('news.bulk_published_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    
                    BulkAction::make('unpublish')
                        ->label(__('news.unpublish_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_published' => false]);
                            
                            Notification::make()
                                ->title(__('news.bulk_unpublished_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'view' => Pages\ViewNews::route('/{record}'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
