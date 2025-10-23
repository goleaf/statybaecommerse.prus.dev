<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Filament\Resources\NewsCommentResource\Pages;
use App\Models\NewsComment;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\VisibleScope;
use BackedEnum;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use UnitEnum;

final class NewsCommentResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    public static function getNavigationGroup(): \Filament\Navigation\NavigationGroup|array|string|null
    {
        return 'Content';
    }

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
        return $schema->schema([
            SchemaSection::make(__('admin.news_comments.basic_information'))
                ->description(__('admin.news_comments.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('news_id')
                                ->label(__('admin.news_comments.news'))
                                ->relationship(
                                    name: 'news',
                                    titleAttribute: 'title',
                                    modifyQueryUsing: fn (Builder $query): Builder => $query->withoutGlobalScopes([
                                        ActiveScope::class,
                                        ApprovedScope::class,
                                        VisibleScope::class,
                                    ])
                                )
                                ->required()
                                ->searchable()
                                ->preload()
                                ->live(),
                            Select::make('parent_id')
                                ->label(__('admin.news_comments.parent_comment'))
                                ->options(function (Get $get, ?NewsComment $record): array {
                                    $newsId = $get('news_id') ?? $record?->news_id;

                                    if (! $newsId) {
                                        return [];
                                    }

                                    $query = NewsComment::query()
                                        ->withoutGlobalScopes([
                                            ActiveScope::class,
                                            ApprovedScope::class,
                                            VisibleScope::class,
                                        ])
                                        ->where('news_id', $newsId)
                                        ->orderBy('created_at');

                                    if ($record?->exists) {
                                        $query->whereKeyNot($record->getKey());
                                    }

                                    return $query->pluck('author_name', 'id')->all();
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                    SchemaGrid::make(2)
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
                    SchemaGrid::make(2)
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
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('news.title')
                    ->label(__('admin.news_comments.news'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state) || $state === '') {
                            return null;
                        }

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

                        if (! is_string($state) || $state === '') {
                            return null;
                        }

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
                    ->relationship(
                        'news',
                        'title',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->withoutGlobalScopes([
                            ActiveScope::class,
                            ApprovedScope::class,
                            VisibleScope::class,
                        ])
                    )
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
                    ->options(fn (): array => NewsComment::query()
                        ->withoutGlobalScopes([
                            ActiveScope::class,
                            ApprovedScope::class,
                            VisibleScope::class,
                        ])
                        ->orderBy('author_name')
                        ->pluck('author_name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_approval')
                    ->label(fn (NewsComment $record): string => $record->is_approved
                        ? __('admin.news_comments.disapprove')
                        : __('admin.news_comments.approve'))
                    ->icon(fn (NewsComment $record): string => $record->is_approved
                        ? 'heroicon-o-x-mark'
                        : 'heroicon-o-check')
                    ->requiresConfirmation()
                    ->modalHeading(fn (NewsComment $record): string => $record->is_approved
                        ? __('admin.news_comments.confirm_disapprove_heading')
                        : __('admin.news_comments.confirm_approve_heading'))
                    ->modalDescription(fn (NewsComment $record): string => $record->is_approved
                        ? __('admin.news_comments.confirm_disapprove_description')
                        : __('admin.news_comments.confirm_approve_description'))
                    ->action(function (NewsComment $record): void {
                        $record->forceFill([
                            'is_approved' => ! $record->is_approved,
                        ])->save();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('admin.news_comments.approve_selected'))
                        ->icon('heroicon-o-check')
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_approved' => true]);
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('disapprove')
                        ->label(__('admin.news_comments.disapprove_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->action(function (EloquentCollection $records): void {
                            $records->each->update(['is_approved' => false]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            ApprovedScope::class,
            VisibleScope::class,
        ]);
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
            'index'  => Pages\ListNewsComments::route('/'),
            'create' => Pages\CreateNewsComment::route('/create'),
            'view'   => Pages\ViewNewsComment::route('/{record}'),
            'edit'   => Pages\EditNewsComment::route('/{record}/edit'),
        ];
    }
}
