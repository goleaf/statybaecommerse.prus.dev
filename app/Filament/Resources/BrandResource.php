<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Support\Authorization\AuthorizationMatrix;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Pixelpeter\FilamentLanguageTabs\Forms\Components\LanguageTabs;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class BrandResource extends Resource
{
    use HasNav;

    protected static ?string $model = Brand::class;

    public static function canAccess(): bool
    {
        return AuthorizationMatrix::check('brands', 'viewAny');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('brands', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('brands', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('brands', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('brands', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('brands', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('brands', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('brands', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('brands', 'update');
    }

    /**
     * @return Builder<Brand>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Explicitly strip the enabled and active visibility scopes so that
        // diagnostic tooling operating through Filament can inspect every brand
        // record regardless of the public storefront filters applied at the
        // model level.
        return $query
            ->withoutGlobalScope(new EnabledScope())
            ->withoutGlobalScope(new ActiveScope());
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin/brands.model.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('admin/brands.model.singular');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('brands.basic_information'))
                ->components([
                    LanguageTabs::make([
                        TextInput::make('name')
                            ->label(__('brands.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('brands.slug'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('brands.description'))
                            ->rows(3),
                    ]),
                    TextInput::make('website')
                        ->label(__('admin/brands.fields.website'))
                        ->url()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('admin/brands.fields.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin/brands.sections.media'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('admin/brands.fields.logo'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->collection('logo')
                        ->maxFiles(1)
                        ->preserveFilenames()
                        ->visibility('private'),
                    FileUpload::make('banner')
                        ->label(__('admin/brands.fields.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->collection('banner')
                        ->maxFiles(1)
                        ->preserveFilenames()
                        ->visibility('private'),
                ]),
            Section::make(__('admin/brands.sections.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('admin/brands.fields.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('admin/brands.fields.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
            Section::make(__('admin/brands.sections.settings'))
                ->schema([
                    Grid::make(3)
                        ->components([
                            Toggle::make('is_enabled')
                                ->label(__('admin/brands.fields.is_enabled'))
                                ->default(true),
                            Toggle::make('is_active')
                                ->label(__('admin/brands.fields.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('admin/brands.fields.is_visible')),
                            Toggle::make('is_featured')
                                ->label(__('admin/brands.fields.is_featured')),
                        ]),
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
                ImageColumn::make('logo')
                    ->label(__('admin/brands.fields.logo'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('admin/brands.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('admin/brands.fields.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('admin/brands.fields.products_count'))
                    ->counts('products')
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('admin/brands.fields.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('admin/brands.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label(__('admin/brands.fields.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('admin/brands.fields.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin/brands.fields.created_at'))
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->label(__('admin/brands.fields.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('enabled')
                    ->label(__('admin/brands.filters.enabled_only'))
                    ->queries(
                        fn (Builder $query) => $query->where('is_enabled', true),
                        fn (Builder $query) => $query->where('is_enabled', false),
                    )
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('admin/brands.filters.featured_only'))
                    ->falseLabel(__('admin/brands.filters.not_featured'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->trueLabel(__('admin/brands.filters.visible_only'))
                    ->falseLabel(__('admin/brands.filters.hidden_only'))
                    ->native(false),
                TrashedFilter::make(),
                Filter::make('with_products')
                    ->label(__('admin/brands.filters.with_products'))
                    ->query(fn (Builder $query) => $query->whereHas('products')),
                Filter::make('without_products')
                    ->label(__('admin/brands.filters.without_products'))
                    ->query(fn (Builder $query) => $query->whereDoesntHave('products')),
                Filter::make('with_website')
                    ->label(__('admin/brands.filters.with_website'))
                    ->query(fn (Builder $query) => $query->whereNotNull('website')->where('website', '!=', '')),
                Filter::make('recent')
                    ->label(__('admin/brands.filters.recent'))
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('brands', 'view')),
                EditAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                DeleteAction::make()
                    ->visible(fn () => AuthorizationMatrix::check('brands', 'delete')),
                Action::make('toggle_active')
                    ->label(fn (Brand $record): string => $record->is_active ? __('admin/brands.actions.deactivate') : __('admin/brands.actions.activate'))
                    ->icon(fn (Brand $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Brand $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Brand $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('admin/brands.notifications.activated') : __('admin/brands.notifications.deactivated'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                Action::make('toggle_featured')
                    ->label(fn (Brand $record): string => $record->is_featured ? __('admin/brands.actions.unfeature') : __('admin/brands.actions.feature'))
                    ->icon(fn (Brand $record): string => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn (Brand $record): string => $record->is_featured ? 'warning' : 'success')
                    ->action(function (Brand $record): void {
                        $record->update(['is_featured' => ! $record->is_featured]);

                        Notification::make()
                            ->title($record->is_featured ? __('admin/brands.notifications.featured_enabled') : __('admin/brands.notifications.featured_disabled'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'delete')),
                    RestoreBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'delete')),
                    BulkAction::make('enable')
                        ->label(__('admin/brands.actions.enable_selected'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            if ($ids->isNotEmpty()) {
                                DB::table('brands')->whereIn('id', $ids->all())->update(['is_enabled' => true]);
                            }
                            Notification::make()
                                ->title(__('admin/brands.notifications.bulk_enabled'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                    BulkAction::make('disable')
                        ->label(__('admin/brands.actions.disable_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            if ($ids->isNotEmpty()) {
                                DB::table('brands')->whereIn('id', $ids->all())->update(['is_enabled' => false]);
                            }
                            Notification::make()
                                ->title(__('admin/brands.notifications.bulk_disabled'))
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                    BulkAction::make('feature')
                        ->label(__('admin/brands.actions.feature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('admin/brands.notifications.bulk_featured'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                    BulkAction::make('unfeature')
                        ->label(__('admin/brands.actions.unfeature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('admin/brands.notifications.bulk_unfeatured'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->visible(fn () => AuthorizationMatrix::check('brands', 'update')),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return static::authorizeBrand(null, 'viewAny');
    }

    public static function canCreate(): bool
    {
        return static::authorizeBrand(null, 'create');
    }

    public static function canView(Brand $record): bool
    {
        return static::authorizeBrand($record, 'view');
    }

    public static function canEdit(Brand $record): bool
    {
        return static::authorizeBrand($record, 'update');
    }

    public static function canDelete(Brand $record): bool
    {
        return static::authorizeBrand($record, 'delete');
    }

    public static function canRestore(Brand $record): bool
    {
        return static::authorizeBrand($record, 'restore');
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
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view'   => Pages\ViewBrand::route('/{record}'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}