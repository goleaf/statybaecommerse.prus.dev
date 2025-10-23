<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use UnitEnum;

use Filament\Schemas\Schema;
use UnitEnum;
final class CategoryResource extends Resource
{
    /** @var string|BackedEnum|null Keep compatibility with Filament v4 navigation icon expectations. */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    /** @var string|BackedEnum|null Align the resource under the Products navigation section. */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Products;

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

    public static function form(Schema $form): Schema
    {
        return $schema->schema([
            Section::make(__('categories.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('categories.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, Set $set): void {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug($state));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label(__('categories.slug'))
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
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
            Section::make(__('categories.media'))
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
            Section::make(__('categories.appearance'))
                ->components([
                    Grid::make(2)
                        ->components([
                            ColorPicker::make('color')
                                ->label(__('categories.color'))
                                ->hex(),
                            TextInput::make('sort_order')
                                ->label(__('categories.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ]),
                ]),
            Section::make(__('categories.seo'))
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
            Section::make(__('categories.settings'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('categories.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('categories.is_featured')),
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
                        $record = $column->getRecord();
                        if ($record->parent) {
                            // Use the Str helper to avoid deprecated string helper aliases while building the breadcrumb label.
                            return Str::of($record->parent->name)
                                ->append(' → ')
                                ->append((string) $state)
                                ->toString();
                        }

                        return $state;
                    }),
                TextColumn::make('slug')
                    ->label(__('categories.slug'))
                    ->copyable()
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
                TernaryFilter::make('is_active')
                    ->trueLabel(__('categories.active_only'))
                    ->falseLabel(__('categories.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('categories.featured_only'))
                    ->falseLabel(__('categories.not_featured'))
                    ->native(false),
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'view')),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('categories', 'update')),
                Tables\Actions\Action::make('toggle_active')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('categories', 'delete')),
                    Tables\Actions\BulkAction::make('activate')
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
                    Tables\Actions\BulkAction::make('deactivate')
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

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::authorizeCategory(null, 'viewAny');
    }

    public static function canCreate(): bool
    {
        return static::authorizeCategory(null, 'create');
    }

    public static function canView(Category $record): bool
    {
        return static::authorizeCategory($record, 'view');
    }

    public static function canEdit(Category $record): bool
    {
        return static::authorizeCategory($record, 'update');
    }

    public static function canDelete(Category $record): bool
    {
        return static::authorizeCategory($record, 'delete');
    }

    public static function canRestore(Category $record): bool
    {
        return static::authorizeCategory($record, 'restore');
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
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view'   => Pages\ViewCategory::route('/{record}'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}