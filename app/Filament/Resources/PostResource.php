<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\ModerationState;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Seo\LocaleUrlGenerator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * @var string|BackedEnum|null
     */
    

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
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('posts.sections.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('posts.fields.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state): void {
                                        if (! $get('slug') && filled($state)) {
                                            $set('slug', \Str::slug($state));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->label(__('posts.fields.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Post::class, 'slug', ignoreRecord: true),
                            ]),
                        Textarea::make('excerpt')
                            ->label(__('posts.fields.excerpt'))
                            ->maxLength(500)
                            ->rows(3),
                        RichEditor::make('content')
                            ->label(__('posts.fields.content'))
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make(__('posts.sections.media'))
                    ->schema([
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
                Section::make(__('posts.sections.seo'))
                    ->schema([
                        TextInput::make('meta_title')
                            ->label(__('posts.fields.meta_title'))
                            ->maxLength(255),
                        Textarea::make('meta_description')
                            ->label(__('posts.fields.meta_description'))
                            ->maxLength(160)
                            ->rows(3),
                    ]),
                Section::make(__('posts.sections.settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('posts.fields.status'))
                                    ->options([
                                        'draft' => __('posts.status.draft'),
                                        'published' => __('posts.status.published'),
                                        'archived' => __('posts.status.archived'),
                                    ])
                                    ->default('draft')
                                    ->required()
                                    ->disableOptionWhen('published', fn (): bool => true)
                                    ->helperText(__('posts.status_managed_by_workflow')),
                                Select::make('user_id')
                                    ->label(__('posts.fields.user_id'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                        DateTimePicker::make('published_at')
                            ->label(__('posts.fields.published_at'))
                            ->default(now()),
                        Grid::make(2)
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
                                Flatpickr::makeDateTime('published_at')
                                    ->label(__('posts.fields.published_at'))
                                    ->default(now()),
                            ]),
                        Grid::make(2)
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
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('posts.fields.images'))
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular()
                    ->size(50),
                TextColumn::make('title')
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
                BadgeColumn::make('moderation_state')
                    ->label(__('posts.fields.moderation_state'))
                    ->formatStateUsing(fn (?ModerationState $state): ?string => $state?->label())
                    ->colors([
                        'warning' => fn (?ModerationState $state): bool => $state === ModerationState::Draft,
                        'info' => fn (?ModerationState $state): bool => $state === ModerationState::Review,
                        'success' => fn (?ModerationState $state): bool => $state === ModerationState::Published,
                    ])
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('posts.fields.status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger'  => 'archived',
                    ]),
                IconColumn::make('featured')
                    ->label(__('posts.fields.featured'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                IconColumn::make('is_pinned')
                    ->label(__('posts.fields.is_pinned'))
                    ->boolean()
                    ->trueIcon('heroicon-o-thumbtack')
                    ->falseIcon('heroicon-o-thumbtack')
                    ->trueColor('success')
                    ->falseColor('gray'),
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
                        DateTimePicker::make('published_from')
                            ->label(__('posts.filters.published_from')),
                        DateTimePicker::make('published_until')
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
                Action::make('submit_for_review')
                    ->label(__('moderation.actions.submit_for_review'))
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Post $record): bool => $record->moderation_state === ModerationState::Draft)
                    ->action(function (Post $record): void {
                        $record->update([
                            'moderation_state' => ModerationState::Review,
                            'submitted_for_review_at' => now(),
                            'status' => 'draft',
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
                Action::make('approve')
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
                            throw new \RuntimeException('Approvals require an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $data, $userId): void {
                            $record->approvals()->create([
                                'user_id' => $userId,
                                'decision' => 'approved',
                                'notes' => $data['notes'] ?? null,
                                'decided_at' => now(),
                            ]);

                            $record->update([
                                'moderation_state' => ModerationState::Published,
                                'approved_at' => now(),
                                'approved_by_id' => $userId,
                                'status' => 'published',
                                'published_at' => $record->published_at ?? now(),
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
                Action::make('request_changes')
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
                            throw new \RuntimeException('Return to draft requires an authenticated user.');
                        }

                        DB::transaction(function () use ($record, $data, $userId): void {
                            $record->approvals()->create([
                                'user_id' => $userId,
                                'decision' => 'returned',
                                'notes' => $data['notes'] ?? null,
                                'decided_at' => now(),
                            ]);

                            $record->update([
                                'moderation_state' => ModerationState::Draft,
                                'submitted_for_review_at' => null,
                                'approved_at' => null,
                                'approved_by_id' => null,
                                'status' => 'draft',
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
