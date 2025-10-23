<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;
use App\Enums\ModerationState;
use App\Filament\Components\Combobox;
use App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource\RelationManagers;
use App\Models\News;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use BackedEnum;
use Filament\Forms;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use RuntimeException;

class NewsResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's BackedEnum-aware union expectations.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'News Article';

    protected static ?string $pluralModelLabel = 'News Articles';

    public static function form(Schema $schema): Schema   
    {
        return $schema->components([
            Forms\Components\SchemaSection::make('Article Information')
                ->components([
                    LanguageTabs::make([
                        Forms\Components\TextInput::make('title')
                            ->label(__('news.fields.title'))
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                $set('slug', \Illuminate\Support\Str::slug((string) $state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('news.fields.slug'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('summary')
                            ->label(__('news.fields.excerpt'))
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\RichEditor::make('content')
                            ->label(__('news.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                ])
                ->columns(1),
            Forms\Components\SchemaSection::make('Publishing')
                ->components([
                    SupportFlatpickr::makeDateTime('published_at')
                        ->label(__('news.fields.published_at'))
                        ->default(now()),
                    Forms\Components\TextInput::make('author_name')
                        ->label(__('news.fields.author_name'))
                        ->maxLength(255),
                    Forms\Components\TextInput::make('author_email')
                        ->label(__('news.fields.author_email'))
                        ->email()
                        ->maxLength(255),
                    Forms\Components\Toggle::make('is_visible')
                        ->label(__('news.fields.is_visible'))
                        ->default(false)
                        ->helperText(__('news.visibility_managed'))
                        ->disabled(),
                    Forms\Components\Toggle::make('is_featured')
                        ->label(__('news.fields.is_featured')),
                    Forms\Components\Toggle::make('is_breaking')
                        ->label(__('news.fields.is_breaking')),
                    Forms\Components\Placeholder::make('moderation_state')
                        ->label(__('news.fields.moderation_state'))
                        ->content(fn (?News $record): string => $record?->moderation_state?->label() ?? ModerationState::Draft->label()),
                    Forms\Components\Placeholder::make('submitted_for_review_at')
                        ->label(__('news.fields.submitted_for_review_at'))
                        ->content(fn (?News $record): string => $record?->submitted_for_review_at?->format('Y-m-d H:i') ?? '—'),
                    Forms\Components\Placeholder::make('approved_at')
                        ->label(__('news.fields.approved_at'))
                        ->content(fn (?News $record): string => $record?->approved_at?->format('Y-m-d H:i') ?? '—'),
                    Forms\Components\Placeholder::make('approved_by')
                        ->label(__('news.fields.approved_by'))
                        ->content(fn (?News $record): string => $record?->approvedBy?->name ?? '—'),
                ])
                ->columns(2),
            Forms\Components\SchemaSection::make('SEO & Metadata')
                ->components([
                    LanguageTabs::make([
                        Forms\Components\TextInput::make('meta_title')
                            ->label(__('news.fields.meta_title'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description')
                            ->label(__('news.fields.meta_description'))
                            ->maxLength(500)
                            ->rows(3),
                    ]),
                    Forms\Components\TextInput::make('meta_keywords')
                        ->label(__('news.fields.meta_keywords'))
                        ->maxLength(255),
                ]),
            Forms\Components\SchemaSection::make(__('news.podcast.section_title'))
                ->description(__('news.podcast.section_description'))
                ->collapsible()
                ->collapsed()
                ->components([
                    Forms\Components\TextInput::make('meta_data.podcast_url')
                        ->label(__('news.fields.podcast_url'))
                        ->placeholder('https://share.transistor.fm/s/...')
                        ->maxLength(2048)
                        ->url()
                        ->nullable()
                        ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? trim($state) : null)
                        ->helperText(__('news.podcast.field_help')),
                ]),
            Forms\Components\SchemaSection::make('Categories & Tags')
                ->components([
                    Combobox::make('categories')
                        ->label(__('news.fields.categories'))
                        ->relationship('categories', 'name')
                        ->height('320px')
                        ->translatedLabels(
                            'news.combobox.categories.available',
                            'news.combobox.categories.selected',
                        ),
                    Combobox::make('tags')
                        ->label(__('news.fields.tags'))
                        ->relationship('tags', 'name')
                        ->height('320px')
                        ->translatedLabels(
                            'news.combobox.tags.available',
                            'news.combobox.tags.selected',
                        ),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label(__('news.fields.featured_image'))
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('news.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->formatStateUsing(fn (?string $state, News $record): ?string => $record->trans('title')),
                Tables\Columns\TextColumn::make('author_name')
                    ->label(__('news.fields.author_name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label(__('news.fields.categories'))
                    ->badge()
                    ->separator(','),
                Tables\Columns\BadgeColumn::make('moderation_state')
                    ->label(__('news.fields.moderation_state'))
                    ->formatStateUsing(fn (?ModerationState $state): ?string => $state?->label())
                    ->colors([
                        'warning' => fn (?ModerationState $state): bool => $state === ModerationState::Draft,
                        'info'    => fn (?ModerationState $state): bool => $state === ModerationState::Review,
                        'success' => fn (?ModerationState $state): bool => $state === ModerationState::Published,
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('news.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('news.fields.is_visible'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('news.fields.is_featured'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_breaking')
                    ->label(__('news.fields.is_breaking'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label(__('news.fields.view_count'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('news.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('moderation_state')
                    ->label(__('news.fields.moderation_state'))
                    ->options(ModerationState::options()),
                Tables\Filters\SelectFilter::make('categories')
                    ->relationship('categories', 'name')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('news.fields.is_visible')),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('news.fields.is_featured')),
                Tables\Filters\TernaryFilter::make('is_breaking')
                    ->label(__('news.fields.is_breaking')),
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        SupportFlatpickr::makeDate('published_from')
                            ->label(__('news.filters.published_from')),
                        SupportFlatpickr::makeDate('published_until')
                            ->label(__('news.filters.published_until')),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('submit_for_review')
                    ->label(__('moderation.actions.submit_for_review'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (News $record): bool => $record->moderation_state === ModerationState::Draft)
                    ->action(function (News $record): void {
                        $record->update([
                            'moderation_state'        => ModerationState::Review,
                            'submitted_for_review_at' => now(),
                            'is_visible'              => false,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('submitted_for_review')
                            ->log('News submitted for review');

                        Notification::make()
                            ->title(__('moderation.messages.submitted'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label(__('moderation.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('news.approvals.notes'))
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText(__('news.approvals.notes_help')),
                    ])
                    ->visible(fn (News $record): bool => $record->moderation_state === ModerationState::Review)
                    ->action(function (News $record, array $data): void {
                        $userId = Auth::id();

                        if (! $userId) {
                            throw new RuntimeException('Approvals require an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $userId, $data): void {
                            $record->approvals()->create([
                                'user_id'    => $userId,
                                'decision'   => 'approved',
                                'notes'      => $data['notes'] ?? null,
                                'decided_at' => now(),
                            ]);

                            $record->update([
                                'moderation_state' => ModerationState::Published,
                                'approved_at'      => now(),
                                'approved_by_id'   => $userId,
                                'is_visible'       => true,
                                'published_at'     => $record->published_at ?? now(),
                            ]);
                        });

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('approved')
                            ->log('News approved and published');

                        Notification::make()
                            ->title(__('moderation.messages.approved'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('request_changes')
                    ->label(__('moderation.actions.return_to_draft'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('news.approvals.notes'))
                            ->maxLength(500)
                            ->rows(3)
                            ->required(),
                    ])
                    ->visible(fn (News $record): bool => $record->moderation_state !== ModerationState::Draft)
                    ->action(function (News $record, array $data): void {
                        $userId = Auth::id();

                        if (! $userId) {
                            throw new RuntimeException('Return to draft requires an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $userId, $data): void {
                            $record->approvals()->create([
                                'user_id'    => $userId,
                                'decision'   => 'returned',
                                'notes'      => $data['notes'] ?? null,
                                'decided_at' => now(),
                            ]);

                            $record->update([
                                'moderation_state'        => ModerationState::Draft,
                                'submitted_for_review_at' => null,
                                'approved_at'             => null,
                                'approved_by_id'          => null,
                                'is_visible'              => false,
                            ]);
                        });

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('returned_to_draft')
                            ->log('News returned to draft');

                        Notification::make()
                            ->title(__('moderation.messages.returned'))
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema   
    {
        // Provide the infolist schema using the Filament v4 return type.
        return $schema
            ->components([
                Infolists\Components\SchemaSection::make('Article Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('news.fields.title')),
                        Infolists\Components\TextEntry::make('slug')
                            ->label(__('news.fields.slug'))
                            ->copyable(),
                        Infolists\Components\TextEntry::make('excerpt')
                            ->label(__('news.fields.excerpt'))
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('content')
                            ->label(__('news.fields.content'))
                            ->html()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Infolists\Components\SchemaSection::make('Publishing Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('author_name')
                            ->label(__('news.fields.author_name')),
                        Infolists\Components\TextEntry::make('author_email')
                            ->label(__('news.fields.author_email')),
                        Infolists\Components\TextEntry::make('published_at')
                            ->label(__('news.fields.published_at'))
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('moderation_state')
                            ->label(__('news.fields.moderation_state'))
                            ->formatStateUsing(fn (?ModerationState $state): ?string => $state?->label()),
                        Infolists\Components\TextEntry::make('submitted_for_review_at')
                            ->label(__('news.fields.submitted_for_review_at'))
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('approved_at')
                            ->label(__('news.fields.approved_at'))
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('approvedBy.name')
                            ->label(__('news.fields.approved_by')),
                        Infolists\Components\IconEntry::make('is_visible')
                            ->label(__('news.fields.is_visible'))
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->label(__('news.fields.is_featured'))
                            ->boolean(),
                        Infolists\Components\IconEntry::make('is_breaking')
                            ->label(__('news.fields.is_breaking'))
                            ->boolean(),
                        Infolists\Components\TextEntry::make('view_count')
                            ->label(__('news.fields.view_count'))
                            ->numeric(),
                    ])
                    ->columns(3),
                Infolists\Components\SchemaSection::make('Categories & Tags')
                    ->schema([
                        Infolists\Components\TextEntry::make('categories.name')
                            ->label(__('news.fields.categories'))
                            ->badge()
                            ->separator(','),
                        Infolists\Components\TextEntry::make('tags.name')
                            ->label(__('news.fields.tags'))
                            ->badge()
                            ->separator(','),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ApprovalsRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'view'   => Pages\ViewNews::route('/{record}'),
            'edit'   => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (in_array(SoftDeletes::class, class_uses_recursive(static::getModel()))) {
            $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        }

        return $query;
    }
}
