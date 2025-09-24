<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * PostResource
 *
 * Filament v4 resource for Post management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PostResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Content';
    }

    protected static ?string $model = Post::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * @var string|\BackedEnum|null
     */
    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-document-text';
    }

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('posts.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('posts.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('posts.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('posts.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('posts.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state): void {
                                        if (! $get('slug') && filled($state)) {
                                            $set('slug', \Str::slug($state));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->label(__('posts.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true),
                            ]),
                        Textarea::make('excerpt')
                            ->label(__('posts.excerpt'))
                            ->maxLength(500)
                            ->rows(3),
                        RichEditor::make('content')
                            ->label(__('posts.content'))
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('posts.media'))
                    ->schema([
                        FileUpload::make('featured_image')
                            ->label(__('posts.featured_image'))
                            ->image()
                            ->directory('posts')
                            ->visibility('public'),
                        FileUpload::make('gallery')
                            ->label(__('posts.gallery'))
                            ->image()
                            ->multiple()
                            ->directory('posts/gallery')
                            ->visibility('public'),
                    ]),
                Section::make(__('posts.seo'))
                    ->schema([
                        TextInput::make('meta_title')
                            ->label(__('posts.meta_title'))
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label(__('posts.meta_description'))
                            ->maxLength(160)
                            ->rows(3),
                    ]),
                Section::make(__('posts.settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('posts.status'))
                                    ->options([
                                        'draft' => __('posts.status.draft'),
                                        'published' => __('posts.status.published'),
                                        'archived' => __('posts.status.archived'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                                Select::make('user_id')
                                    ->label(__('posts.author'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        DateTimePicker::make('published_at')
                            ->label(__('posts.published_at'))
                            ->default(now()),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('featured')
                                    ->label(__('posts.featured')),
                                Toggle::make('is_pinned')
                                    ->label(__('posts.is_pinned')),
                                Toggle::make('allow_comments')
                                    ->label(__('posts.allow_comments'))
                                    ->default(true),
                            ]),
                        TagsInput::make('tags')
                            ->label(__('posts.tags'))
                            ->placeholder(__('posts.add_tag')),
                    ]),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label(__('posts.featured_image'))
                    ->circular()
                    ->size(50),
                TextColumn::make('title')
                    ->label(__('posts.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label(__('posts.author'))
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('posts.status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),
                IconColumn::make('featured')
                    ->label(__('posts.featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_pinned')
                    ->label(__('posts.is_pinned'))
                    ->boolean()
                    ->trueIcon('heroicon-o-thumbtack')
                    ->falseIcon('heroicon-o-thumbtack')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('published_at')
                    ->label(__('posts.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('posts.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('posts.status'))
                    ->options([
                        'draft' => __('posts.status.draft'),
                        'published' => __('posts.status.published'),
                        'archived' => __('posts.status.archived'),
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('posts.author'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('featured')
                    ->label(__('posts.featured'))
                    ->placeholder(__('posts.all_records'))
                    ->trueLabel(__('posts.featured_only'))
                    ->falseLabel(__('posts.non_featured_only')),
                TernaryFilter::make('is_pinned')
                    ->label(__('posts.is_pinned'))
                    ->placeholder(__('posts.all_records'))
                    ->trueLabel(__('posts.pinned_only'))
                    ->falseLabel(__('posts.non_pinned_only')),
                Filter::make('published_at')
                    ->form([
                        DateTimePicker::make('published_from')
                            ->label(__('posts.published_from')),
                        DateTimePicker::make('published_until')
                            ->label(__('posts.published_until')),
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
                    ->label(__('posts.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn (Post $record): bool => $record->status !== 'published')
                    ->action(function (Post $record): void {
                        $record->update([
                            'status' => 'published',
                            'published_at' => now(),
                        ]);
                        Notification::make()
                            ->title(__('posts.published_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('unpublish')
                    ->label(__('posts.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (Post $record): bool => $record->status === 'published')
                    ->action(function (Post $record): void {
                        $record->update(['status' => 'draft']);
                        Notification::make()
                            ->title(__('posts.unpublished_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label(__('posts.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'status' => 'published',
                                'published_at' => now(),
                            ]);
                            Notification::make()
                                ->title(__('posts.bulk_published_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unpublish')
                        ->label(__('posts.unpublish_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'draft']);
                            Notification::make()
                                ->title(__('posts.bulk_unpublished_success'))
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
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
