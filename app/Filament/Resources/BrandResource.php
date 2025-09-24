<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
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
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->can('browse_brands') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([EnabledScope::class, ActiveScope::class]);
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('brands.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('brands.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('brands.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('brands.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, SchemaSet $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null),
                            TextInput::make('slug')
                                ->label(__('brands.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    TextInput::make('website')
                        ->label(__('brands.website'))
                        ->url()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label(__('brands.description'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Section::make(__('brands.media'))
                ->schema([
                    FileUpload::make('logo')
                        ->label(__('brands.logo'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '16:9',
                            '4:3',
                        ])
                        ->directory('brands/logos')
                        ->visibility('public'),
                    FileUpload::make('banner')
                        ->label(__('brands.banner'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '21:9',
                        ])
                        ->directory('brands/banners')
                        ->visibility('public'),
                ]),
            Section::make(__('brands.seo'))
                ->schema([
                    TextInput::make('seo_title')
                        ->label(__('brands.seo_title'))
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label(__('brands.seo_description'))
                        ->rows(2)
                        ->maxLength(500),
                ]),
            Section::make(__('brands.settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_enabled')
                                ->label(__('brands.is_enabled'))
                                ->default(true),
                            Toggle::make('is_active')
                                ->label(__('brands.is_active'))
                                ->default(true),
                            Toggle::make('is_visible')
                                ->label(__('brands.is_visible')),
                            Toggle::make('is_featured')
                                ->label(__('brands.is_featured')),
                        ]),
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
                ImageColumn::make('logo')
                    ->label(__('brands.logo'))
                    ->circular()
                    ->size(40),
                TextColumn::make('name')
                    ->label(__('brands.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('brands.slug'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                IconColumn::make('is_enabled')
                    ->label(__('brands.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('brands.is_active'))
                    ->boolean(),
                IconColumn::make('is_visible')
                    ->label(__('brands.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('brands.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('brands.created_at'))
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->label(__('brands.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('enabled')
                    ->label(__('brands.enabled_only'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_enabled', true),
                        false: fn (Builder $query) => $query->where('is_enabled', false),
                        blank: fn (Builder $query) => $query
                    )
                    ->native(false),
                TernaryFilter::make('is_featured')
                    ->trueLabel(__('brands.featured_only'))
                    ->falseLabel(__('brands.not_featured'))
                    ->native(false),
                TernaryFilter::make('is_visible')
                    ->trueLabel(__('brands.visible_only'))
                    ->falseLabel(__('brands.hidden_only'))
                    ->native(false),
                TrashedFilter::make(),
                Filter::make('with_products')
                    ->label(__('brands.with_products'))
                    ->query(fn (Builder $query) => $query->whereHas('products')),
                Filter::make('without_products')
                    ->label(__('brands.without_products'))
                    ->query(fn (Builder $query) => $query->whereDoesntHave('products')),
                Filter::make('with_website')
                    ->label(__('brands.with_website'))
                    ->query(fn (Builder $query) => $query->whereNotNull('website')->where('website', '!=', '')),
                Filter::make('recent')
                    ->label(__('brands.recent'))
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Actions\ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(fn (Brand $record): string => $record->is_active ? __('brands.deactivate') : __('brands.activate'))
                    ->icon(fn (Brand $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Brand $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Brand $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('brands.activated_successfully') : __('brands.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_featured')
                    ->label(fn (Brand $record): string => $record->is_featured ? __('brands.unfeature') : __('brands.feature'))
                    ->icon(fn (Brand $record): string => $record->is_featured ? 'heroicon-o-star' : 'heroicon-o-star')
                    ->color(fn (Brand $record): string => $record->is_featured ? 'warning' : 'success')
                    ->action(function (Brand $record): void {
                        $record->update(['is_featured' => ! $record->is_featured]);

                        Notification::make()
                            ->title($record->is_featured ? __('brands.featured_enabled') : __('brands.featured_disabled'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    BulkAction::make('enable')
                        ->label(__('brands.enable_selected'))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            if ($ids instanceof BaseCollection && $ids->isNotEmpty()) {
                                DB::table('brands')->whereIn('id', $ids->all())->update(['is_enabled' => true]);
                            }
                            Notification::make()
                                ->title(__('brands.bulk_enabled_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('disable')
                        ->label(__('brands.disable_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $ids = $records->pluck('id');
                            if ($ids instanceof BaseCollection && $ids->isNotEmpty()) {
                                DB::table('brands')->whereIn('id', $ids->all())->update(['is_enabled' => false]);
                            }
                            Notification::make()
                                ->title(__('brands.bulk_disabled_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('brands.feature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('brands.bulk_featured_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unfeature')
                        ->label(__('brands.unfeature_selected'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('brands.bulk_unfeatured_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view' => Pages\ViewBrand::route('/{record}'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
