<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use App\Support\Concerns\HasNav;
use Filament\Schemas\Schema;
use App\Enums\ModerationState;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use App\Support\Seo\LocaleUrlGenerator;
use Awcodes\BadgeableColumn\Components\Badge;
use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use pxlrbt\FilamentExcel\Actions\ExportBulkAction as ExcelExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use RuntimeException;
use UnitEnum;

/**
 * PostResource
 *
 * Filament v4 resource for Post management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PostResource extends Resource
{
    use HasNav;

    

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
            ->components([
                SchemaSection::make(__('posts.sections.basic_information'))
                    ->components([
                        LanguageTabs::make([
                            TextInput::make('title')
                                ->label(__('posts.fields.title'))
                                ->required()
                                ->maxLength(255)
                                ->live()
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state): void {
                                    if (! $get('slug') && filled($state)) {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                            Textarea::make('excerpt')
                                ->label(__('posts.fields.excerpt'))
                                ->maxLength(500)
                                ->rows(3),
                            RichEditor::make('content')
                                ->label(__('posts.fields.content'))
                                ->required()
                                ->columnSpanFull(),
                        ]),
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('posts.fields.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state): void {
                                        if (! $get('slug') && filled($state)) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->label(__('posts.fields.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true),
                                Select::make('user_id')
                                    ->label(__('posts.fields.user_id'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                    ]),
                SchemaSection::make(__('posts.sections.media'))
                    ->components([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('posts.fields.images'))
                            ->collection('images')
                            ->image()
                            ->singleFile(),
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->label(__('posts.fields.gallery'))
                            ->collection('gallery')
                            ->image()
                            ->multiple(),
                    ]),
                SchemaSection::make(__('posts.sections.seo'))
                    ->components([
                        LanguageTabs::make([
                            TextInput::make('meta_title')
                                ->label(__('posts.fields.meta_title'))
                                ->maxLength(255),
                            Textarea::make('meta_description')
                                ->label(__('posts.fields.meta_description'))
                                ->maxLength(160)
                                ->rows(3),
                        ]),
                    ]),
                SchemaSection::make(__('posts.sections.settings'))
                    ->components([
                        SchemaGrid::make(2)
                            ->components([
                                Select::make('status')
                                    ->label(__('posts.fields.status'))
                                    ->options([
                                        'draft'     => __('posts.status.draft'),
                                        'published' => __('posts.status.published'),
                                        'archived'  => __('posts.status.archived'),
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->disableOptionWhen(fn (string $value): bool => $value === 'published')
                                    ->helperText(__('posts.status_managed_by_workflow')),
                                SupportFlatpickr::makeDateTime('published_at')
                                    ->label(__('posts.fields.published_at'))
                                    ->default(now()),
                            ]),
                        SchemaGrid::make(2)
                            ->components([
                                Toggle::make('featured')
                                    ->label(__('posts.fields.featured')),
                                Toggle::make('is_pinned')
                                    ->label(__('posts.fields.is_pinned')),
                                Toggle::make('allow_comments')
                                    ->label(__('posts.fields.allow_comments'))
                                    ->default(true),
                            ]),
                        Forms\Components\Placeholder::make('moderation_state')
                            ->label(__('posts.fields.moderation_state'))
                            ->content(fn (?Post $record): string => $record?->moderation_state?->label() ?? ModerationState::Draft->label())
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('submitted_for_review_at')
                            ->label(__('posts.fields.submitted_for_review_at'))
                            ->content(fn (?Post $record): string => $record?->submitted_for_review_at?->format('Y-m-d H:i') ?? '—'),
                        Forms\Components\Placeholder::make('approved_at')
                            ->label(__('posts.fields.approved_at'))
                            ->content(fn (?Post $record): string => $record?->approved_at?->format('Y-m-d H:i') ?? '—'),
                        Forms\Components\Placeholder::make('approved_by')
                            ->label(__('posts.fields.approved_by'))
                            ->content(fn (?Post $record): string => $record?->approvedBy?->name ?? '—'),
                        TagsInput::make('tags')
                            ->label(__('posts.tags'))
                            ->placeholder(__('posts.add_tag'))
                            ->formatStateUsing(static function ($state): array {
                                if (blank($state)) {
                                    return [];
                                }

                                if (is_array($state)) {
                                    return array_values(array_filter(array_map(
                                        static fn ($tag): string => trim((string) $tag),
                                        $state,
                                    )));
                                }

                                return collect(explode(',', (string) $state))
                                    ->map(static fn ($tag): string => trim($tag))
                                    ->filter()
                                    ->values()
                                    ->all();
                            })
                            ->dehydrateStateUsing(static function ($state): ?string {
                                if (blank($state)) {
                                    return null;
                                }

                                $tags = collect(is_array($state) ? $state : explode(',', (string) $state))
                                    ->map(static fn ($tag): string => trim((string) $tag))
                                    ->filter();

                                return $tags->isEmpty() ? null : $tags->implode(', ');
                            }),
                    ]),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('posts.fields.images'))
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular()
                    ->size(50),
                BadgeableColumn::make('title')
                    ->label(__('posts.fields.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->formatStateUsing(fn (?string $state, Post $record): ?string => $record->getTranslatedTitle()),
                ViewColumn::make('quick_links')
                    ->label(__('Quick links'))
                    ->view('filament.tables.columns.list-group')
                    ->state(function (Post $record): array {
                        $localeUrlGenerator = app(LocaleUrlGenerator::class);
                        $locales = collect($localeUrlGenerator->supportedLocales());

                        return $locales
                            ->map(function (string $locale) use ($record, $localeUrlGenerator): ?array {
                                $slug = method_exists($record, 'getTranslation')
                                    ? ($record->getTranslation('slug', $locale) ?: $record->slug)
                                    : ($record->slug ?? null);

                                $url = $slug
                                    ? $localeUrlGenerator->localizedRoute('localized.posts.show', ['post' => $slug], $locale)
                                    : null;

                                if (! $url && Route::has('frontend.posts.show')) {
                                    $url = route('frontend.posts.show', $record);
                                }

                                if (! $url) {
                                    return null;
                                }

                                $title = method_exists($record, 'getTranslation')
                                    ? ($record->getTranslation('title', $locale) ?: $record->title)
                                    : ($record->getTranslatedTitle($locale) ?: $record->title);

                                return [
                                    'label' => __('View (:locale): :title', [
                                        'locale' => strtoupper($locale),
                                        'title'  => $title,
                                    ]),
                                    'url'   => $url,
                                    'icon'  => 'heroicon-o-document-text',
                                    'color' => 'primary',
                                ];
                            })
                            ->filter()
                            ->values()
                            ->all();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label(__('posts.fields.user_id'))
                    ->sortable()
                    ->searchable(),
                BadgeableColumn::make('moderation_state')
                    ->label(__('posts.fields.moderation_state'))
                    ->formatStateUsing(fn (?ModerationState $state): string => $state?->label() ?? __('posts.badges.moderation_unknown'))
                    ->sortable()
                    ->asPills()
                    ->prefixBadges(function (Post $record): array {
                        // Pair workflow moderation with publishing status for faster review triage.
                        $statusColor = match ($record->status) {
                            'published' => 'success',
                            'archived'  => 'danger',
                            'draft'     => 'warning',
                            default     => 'gray',
                        };

                        return [
                            Badge::make('status')
                                ->label(__('posts.status.' . $record->status))
                                ->color($statusColor),
                        ];
                    })
                    ->suffixBadges(function (Post $record): array {
                        // Surface promotional flags and comment settings inline with moderation.
                        return collect([
                            Badge::make('featured')
                                ->label($record->featured ? __('posts.badges.featured') : __('posts.badges.standard'))
                                ->color($record->featured ? 'warning' : 'gray'),
                            Badge::make('pinned')
                                ->label($record->is_pinned ? __('posts.badges.pinned') : __('posts.badges.not_pinned'))
                                ->color($record->is_pinned ? 'success' : 'gray'),
                            Badge::make('comments')
                                ->label($record->allow_comments ? __('posts.badges.comments_on') : __('posts.badges.comments_off'))
                                ->color($record->allow_comments ? 'primary' : 'gray'),
                        ])->filter()->values()->all();
                    }),
                TextColumn::make('published_at')
                    ->label(__('posts.fields.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('posts.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('moderation_state')
                    ->label(__('posts.fields.moderation_state'))
                    ->options(ModerationState::options()),
                SelectFilter::make('status')
                    ->label(__('posts.fields.status'))
                    ->options([
                        'draft'     => __('posts.status.draft'),
                        'published' => __('posts.status.published'),
                        'archived'  => __('posts.status.archived'),
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('posts.fields.user_id'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('featured')
                    ->label(__('posts.fields.featured'))
                    ->placeholder(__('posts.filters.all_posts'))
                    ->trueLabel(__('posts.filters.featured_only'))
                    ->falseLabel(__('posts.filters.not_featured')),
                TernaryFilter::make('is_pinned')
                    ->label(__('posts.fields.is_pinned'))
                    ->placeholder(__('posts.filters.all_posts'))
                    ->trueLabel(__('posts.filters.pinned_only'))
                    ->falseLabel(__('posts.filters.not_pinned')),
                Filter::make('published_at')
                    ->form([
                        SupportFlatpickr::makeDateTime('published_from')
                            ->label(__('posts.filters.published_from')),
                        SupportFlatpickr::makeDateTime('published_until')
                            ->label(__('posts.filters.published_until')),
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
                TableAction::make('submit_for_review')
                    ->label(__('moderation.actions.submit_for_review'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => $record->moderation_state === ModerationState::Draft)
                    ->action(function (Post $record): void {
                        $record->update([
                            'moderation_state'        => ModerationState::Review,
                            'submitted_for_review_at' => now(),
                            'status'                  => 'draft',
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('submitted_for_review')
                            ->log('Post submitted for review');

                        Notification::make()
                            ->title(__('moderation.messages.submitted'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('approve')
                    ->label(__('moderation.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('posts.approvals.notes'))
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText(__('posts.approvals.notes_help')),
                    ])
                    ->visible(fn (Post $record): bool => $record->moderation_state === ModerationState::Review)
                    ->action(function (Post $record, array $data): void {
                        $userId = Auth::id();

                        if (! $userId) {
                            throw new RuntimeException('Approvals require an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $data, $userId): void {
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
                                'status'           => 'published',
                                'published_at'     => $record->published_at ?? now(),
                            ]);
                        });

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('approved')
                            ->log('Post approved and published');

                        Notification::make()
                            ->title(__('moderation.messages.approved'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('request_changes')
                    ->label(__('moderation.actions.return_to_draft'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('posts.approvals.notes'))
                            ->maxLength(500)
                            ->rows(3)
                            ->required(),
                    ])
                    ->visible(fn (Post $record): bool => $record->moderation_state !== ModerationState::Draft)
                    ->action(function (Post $record, array $data): void {
                        $userId = Auth::id();

                        if (! $userId) {
                            throw new RuntimeException('Return to draft requires an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $data, $userId): void {
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
                                'status'                  => 'draft',
                            ]);
                        });

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('returned_to_draft')
                            ->log('Post returned to draft');

                        Notification::make()
                            ->title(__('moderation.messages.returned'))
                            ->warning()
                            ->send();
                    }),
                TableAction::make('publish')
                    ->label(__('posts.actions.publish'))
                    ->icon('heroicon-o-megaphone')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => ! $record->isPublished() && ! $record->isArchived())
                    ->action(function (Post $record): void {
                        $userId = Auth::id();

                        $record->update([
                            'status'                  => 'published',
                            'moderation_state'        => ModerationState::Published,
                            'submitted_for_review_at' => $record->submitted_for_review_at ?? now(),
                            'approved_at'             => $record->approved_at ?? now(),
                            'approved_by_id'          => $record->approved_by_id ?? $userId,
                            'published_at'            => $record->published_at ?? now(),
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('published')
                            ->log('Post manually published');

                        Notification::make()
                            ->title(__('posts.messages.published'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('unpublish')
                    ->label(__('posts.actions.unpublish'))
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => $record->isPublished())
                    ->action(function (Post $record): void {
                        $record->update([
                            'status'                  => 'draft',
                            'moderation_state'        => ModerationState::Draft,
                            'submitted_for_review_at' => null,
                            'approved_at'             => null,
                            'approved_by_id'          => null,
                            'published_at'            => null,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('unpublished')
                            ->log('Post manually unpublished');

                        Notification::make()
                            ->title(__('posts.messages.unpublished'))
                            ->warning()
                            ->send();
                    }),
                TableAction::make('archive')
                    ->label(__('posts.actions.archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => ! $record->isArchived())
                    ->action(function (Post $record): void {
                        $record->update([
                            'status'                  => 'archived',
                            'moderation_state'        => ModerationState::Draft,
                            'submitted_for_review_at' => null,
                            'approved_at'             => null,
                            'approved_by_id'          => null,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('archived')
                            ->log('Post archived');

                        Notification::make()
                            ->title(__('posts.messages.archived'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('feature')
                    ->label(__('posts.actions.feature'))
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => ! $record->featured)
                    ->action(function (Post $record): void {
                        $record->update([
                            'featured' => true,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('featured')
                            ->log('Post marked as featured');

                        Notification::make()
                            ->title(__('posts.messages.featured'))
                            ->success()
                            ->send();
                    }),
                TableAction::make('unfeature')
                    ->label(__('posts.actions.unfeature'))
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => $record->featured)
                    ->action(function (Post $record): void {
                        $record->update([
                            'featured' => false,
                        ]);

                        activity()
                            ->performedOn($record)
                            ->causedBy(Auth::user())
                            ->event('unfeatured')
                            ->log('Post unmarked as featured');

                        Notification::make()
                            ->title(__('posts.messages.unfeatured'))
                            ->info()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExcelExportBulkAction::make()
                        ->exports([
                            ExcelExport::make('posts_export')
                                ->fromTable()
                                ->withFilename(fn (): string => sprintf('posts-%s', now()->format('Y-m-d-His')))
                                ->withWriterType(Excel::CSV)
                                ->withColumns([
                                    ExcelColumn::make('title')->heading(__('posts.fields.title')),
                                    ExcelColumn::make('slug')->heading(__('posts.fields.slug')),
                                    ExcelColumn::make('status')->heading(__('posts.fields.status')),
                                    ExcelColumn::make('published_at')->heading(__('posts.fields.published_at')),
                                    ExcelColumn::make('user.name')->heading(__('posts.fields.user_id')),
                                ]),
                        ]),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user:id,name']);
    }

    /**
     * @return Builder<Post>
     */
    /**
     * Get the relations for this resource.
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\ApprovalsRelationManager::class,
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view'   => Pages\ViewPost::route('/{record}'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
