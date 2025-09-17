<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
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
    protected static ?string $model = Post::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('posts.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Content->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('posts.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('posts.single');
    }

    /**
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('posts.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->label(__('posts.title_label'))
                                ->required()
                                ->maxLength(255),
                            Select::make('status')
                                ->label(__('posts.status'))
                                ->options([
                                    'draft' => __('posts.status_draft'),
                                    'published' => __('posts.status_published'),
                                ])
                                ->default('draft')
                                ->required(),
                        ]),
                    Textarea::make('content')
                        ->label(__('posts.content'))
                        ->rows(5)
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
                    ->label(__('posts.featured_image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('title')
                    ->label(__('posts.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('author.name')
                    ->label(__('posts.author'))
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('excerpt')
                    ->label(__('posts.excerpt'))
                    ->limit(100)
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('is_published')
                    ->label(__('posts.status'))
                    ->formatStateUsing(fn(bool $state): string => $state ? __('posts.published') : __('posts.draft'))
                    ->colors([
                        'success' => true,
                        'warning' => false,
                    ]),
                IconColumn::make('is_featured')
                    ->label(__('posts.is_featured'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('published_at')
                    ->label(__('posts.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('views_count')
                    ->label(__('posts.views_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('posts.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('posts.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('author_id')
                    ->label(__('posts.author'))
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_published')
                    ->label(__('posts.is_published'))
                    ->boolean()
                    ->trueLabel(__('posts.published_only'))
                    ->falseLabel(__('posts.draft_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->label(__('posts.is_featured'))
                    ->boolean()
                    ->trueLabel(__('posts.featured_only'))
                    ->falseLabel(__('posts.not_featured'))
                    ->native(false),
                Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label(__('posts.published_from')),
                        Forms\Components\DatePicker::make('published_until')
                            ->label(__('posts.published_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['published_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '>=', $date),
                            )
                            ->when(
                                $data['published_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('published_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                TableAction::make('publish')
                    ->label(__('posts.publish'))
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn(Post $record): bool => !$record->is_published)
                    ->action(function (Post $record): void {
                        $record->update([
                            'is_published' => true,
                            'published_at' => now(),
                        ]);

                        Notification::make()
                            ->title(__('posts.published_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                TableAction::make('unpublish')
                    ->label(__('posts.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn(Post $record): bool => $record->is_published)
                    ->action(function (Post $record): void {
                        $record->update(['is_published' => false]);

                        Notification::make()
                            ->title(__('posts.unpublished_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label(__('posts.publish_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update([
                                'is_published' => true,
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
                            $records->each->update(['is_published' => false]);

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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
