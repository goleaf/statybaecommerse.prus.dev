<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers\ChildrenRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\CategoryResource\RelationManagers\TranslationsRelationManager;
use App\Models\Category;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\VisibleScope;
use App\Support\Authorization\AuthorizationMatrix;
use App\Support\Concerns\HasNav;
use BackedEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use UnitEnum;

final class CategoryResource extends Resource
{
    use HasNav;

    /**
     * Aligns the navigation icon with Filament's expectations while keeping the
     * property compatible with enum values when needed.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    /** Align the resource under the Products navigation section. */
    protected static \UnitEnum|string|null $navigationGroup = NavigationGroup::Products->value;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $model = Category::class;

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('categories', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('categories', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('categories', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('categories', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('categories', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('categories', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('categories', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('categories', 'update');
    }

    public static function getPluralModelLabel(): string
    {
        return __('categories.plural');
    }

    public static function getModelLabel(): string
    {
        return __('categories.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('categories.basic_information'))
                ->components([
                    LanguageTabs::make([
                        TextInput::make('name')
                            ->label(__('categories.name'))
                            ->required()
                            ->maxLength(255)
                            // Keep the slug synchronised with the initial name while creating new categories for faster authoring.
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                if ($operation === 'create') {
                                    $slug = Str::slug((string) $state);
                                    $defaultLocale = config('app.locale', 'lt');

                                    $set('slug', $slug);
                                    $set("slug.{$defaultLocale}", $slug);
                                    $set("slug_{$defaultLocale}", $slug);
                                }
                            }),
                        TextInput::make('slug')
                            ->label(__('categories.slug'))
                            ->required()
                            ->maxLength(255)
                            // Enforce clean, URL-friendly slugs and keep them unique for routing safety.
                            ->unique(ignoreRecord: true)
                            ->rule('alpha_dash')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                // Normalise user edits so the slug never diverges from the expected format.
                                $slug = Str::slug((string) $state);
                                $defaultLocale = config('app.locale', 'lt');

                                $set('slug', $slug);
                                $set("slug.{$defaultLocale}", $slug);
                                $set("slug_{$defaultLocale}", $slug);
                            }),
                        Textarea::make('description')
                            ->label(__('categories.description'))
                            ->rows(3),
                        Textarea::make('short_description')
                            ->label(__('categories.short_description'))
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                    Select::make('parent_id')
                        ->label(__('categories.parent_category'))
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                ]),
            SchemaSection::make(__('categories.media'))
                ->components([
                    FileUpload::make('image')
                        ->label(__('categories.image'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->directory('categories/images')
                        ->visibility('private'),
                    FileUpload::make('banner')
                        ->label(__('categories.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->directory('categories/banners')
                        ->visibility('private'),
                ]),
            SchemaSection::make(__('categories.appearance'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            ColorPicker::make('color')
                                ->label(__('categories.color'))
                                ->nullable()
                                ->hex(),
                            TextInput::make('sort_order')
                                ->label(__('categories.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            SchemaSection::make(__('categories.seo'))
                ->components([
                    LanguageTabs::make([
                        TextInput::make('seo_title')
                            ->label(__('categories.seo_title'))
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label(__('categories.seo_description'))
                            ->rows(2)
                            ->maxLength(500),
                    ]),
                ]),
            SchemaSection::make(__('categories.settings'))
                ->components([
                    SchemaGrid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('categories.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('categories.is_visible'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('categories.is_featured')),
                            Toggle::make('is_enabled')
                                ->label(__('categories.is_enabled'))
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
                ImageColumn::make('image')
                    ->label(__('categories.image'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('categories.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        /** @var Category|null $record */
                        $record = $column->getRecord();

                        if ($record?->parent) {
                            // Use the Str helper to avoid deprecated string helper aliases while building the breadcrumb label.
                            return Str::of($record->parent->name)
                                ->append(' → ')
                                ->append((string) $state)
                                ->toString();
                        }

                        return is_string($state) ? $state : null;
                    }),
                TextColumn::make('slug')
                    ->label(__('categories.slug'))
                    ->copyable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('categories.color'))
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->label(__('categories.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('children_count')
                    ->label(__('categories.subcategories_count'))
                    ->counts('children'),
                IconColumn::make('is_active')
                    ->label(__('categories.is_active'))
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label(__('categories.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('categories.is_featured'))
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label(__('categories.sort_order'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('categories.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('categories.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('categories.parent_category'))
                    ->relationship('parent', 'name')
                    ->preload(),
                SelectFilter::make('is_active')
                    ->label(__('categories.status'))
                    ->options([
                        '1' => __('categories.active_only'),
                        '0' => __('categories.inactive_only'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::applyBooleanSelectFilter($query, $data, 'is_active')),
                SelectFilter::make('is_visible')
                    ->label(__('categories.visibility'))
                    ->options([
                        '1' => __('categories.visible_only'),
                        '0' => __('categories.hidden_only'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::applyBooleanSelectFilter($query, $data, 'is_visible')),
                SelectFilter::make('is_featured')
                    ->label(__('categories.featured_status'))
                    ->options([
                        '1' => __('categories.featured_only'),
                        '0' => __('categories.not_featured'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => self::applyBooleanSelectFilter($query, $data, 'is_featured')),
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'view')),
                EditAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
                DeleteAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'delete')),
                Action::make('toggle_active')
                    ->label(fn (Category $record): string => $record->is_active ? __('categories.deactivate') : __('categories.activate'))
                    ->icon(fn (Category $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Category $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Category $record): void {
                        // Simple toggle keeps business logic within the action while keeping tests deterministic.
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('categories.activated_successfully') : __('categories.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
                Action::make('toggle_visible')
                    ->label(fn (Category $record): string => $record->is_visible ? __('categories.hide') : __('categories.show'))
                    ->icon(fn (Category $record): string => $record->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Category $record): string => $record->is_visible ? 'warning' : 'success')
                    ->action(function (Category $record): void {
                        // Allow merchandisers to quickly adjust storefront visibility without leaving the list view.
                        $record->update(['is_visible' => ! $record->is_visible]);
                        Notification::make()
                            ->title($record->is_visible ? __('categories.made_visible_successfully') : __('categories.hidden_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('categories', 'delete')),
                    BulkAction::make('activate')
                        ->label(__('categories.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            // Batch activate keeps UI consistent for administrators managing seasonal categories.
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('categories.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
                    BulkAction::make('deactivate')
                        ->label(__('categories.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            // Batch deactivate leverages the same pattern for clarity when cleaning up catalog entries.
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('categories.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    /**
     * Normalise boolean select filter values before applying them to the query.
     */
    private static function applyBooleanSelectFilter(Builder $query, array $data, string $column): Builder
    {
        $value = $data['value'] ?? null;

        if ($value === null || $value === '') {
            return $query;
        }

        $normalised = self::normaliseBooleanFilterValue($value);

        if ($normalised === null) {
            return $query;
        }

        return $query->where($query->qualifyColumn($column), $normalised);
    }

    private static function normaliseBooleanFilterValue(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => null,
            };
        }

        if (is_string($value)) {
            $normalised = strtolower(trim($value));

            return match ($normalised) {
                '1', 'true', 'yes', 'on'   => true,
                '0', 'false', 'no', 'off' => false,
                default                   => null,
            };
        }

        return null;
    }

    public static function getRelations(): array
    {
        return [
            // Expose child taxonomy management for hierarchical structures.
            ChildrenRelationManager::class,
            // Surface product linkage management to keep catalog assignments inline.
            ProductsRelationManager::class,
            // Provide localized content editors to manage translated attributes.
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view'   => Pages\ViewCategory::route('/{record}'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    /**
     * @return Builder<Category>
     */
    public static function getEloquentQuery(): Builder
    {
        // Lift the storefront-specific scopes so administrators can manage inactive or hidden categories directly.
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
                EnabledScope::class,
                VisibleScope::class,
            ]);
    }
}
