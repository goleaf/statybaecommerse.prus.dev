<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\NewsCommentResource\Pages;
use App\Models\News;
use App\Models\NewsComment;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
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

final class NewsCommentResource extends Resource
{
    protected static ?string $model = NewsComment::class;
    
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;
    
    protected static ?int $navigationSort = 3;
    
    public static function getNavigationLabel(): string
    {
        return __('admin.news_comments.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.news_comments.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.news_comments.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.news_comments.basic_information'))
                    ->description(__('admin.news_comments.basic_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('news_id')
                                    ->label(__('admin.news_comments.news'))
                                    ->options(News::pluck('title', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Select::make('parent_id')
                                    ->label(__('admin.news_comments.parent_comment'))
                                    ->options(NewsComment::pluck('author_name', 'id'))
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('author_name')
                                    ->label(__('admin.news_comments.author_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('author_email')
                                    ->label(__('admin.news_comments.author_email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Textarea::make('content')
                            ->label(__('admin.news_comments.content'))
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_approved')
                                    ->label(__('admin.news_comments.is_approved'))
                                    ->default(false),

                                Toggle::make('is_visible')
                                    ->label(__('admin.news_comments.is_visible'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('news.title')
                    ->label(__('admin.news_comments.news'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('author_name')
                    ->label(__('admin.news_comments.author_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('author_email')
                    ->label(__('admin.news_comments.author_email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('content')
                    ->label(__('admin.news_comments.content'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                IconColumn::make('is_approved')
                    ->label(__('admin.news_comments.is_approved'))
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_visible')
                    ->label(__('admin.news_comments.is_visible'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('parent.author_name')
                    ->label(__('admin.news_comments.parent_comment'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label(__('admin.news_comments.news'))
                    ->options(News::pluck('title', 'id'))
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('is_approved')
                    ->label(__('admin.news_comments.is_approved'))
                    ->boolean(),

                TernaryFilter::make('is_visible')
                    ->label(__('admin.news_comments.is_visible'))
                    ->boolean(),

                SelectFilter::make('parent_id')
                    ->label(__('admin.news_comments.parent_comment'))
                    ->options(NewsComment::pluck('author_name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('admin.news_comments.approve_selected'))
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => true]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('disapprove')
                        ->label(__('admin.news_comments.disapprove_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => false]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListNewsComments::route('/'),
            'create' => Pages\CreateNewsComment::route('/create'),
            'view' => Pages\ViewNewsComment::route('/{record}'),
            'edit' => Pages\EditNewsComment::route('/{record}/edit'),
        ];
    }
}
