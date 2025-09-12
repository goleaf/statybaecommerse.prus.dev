<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Enums\NavigationIcon;
use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Filament\Resources\BrandResource\Widgets;
use App\Models\Brand;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static NavigationIcon $navigationIcon = NavigationIcon::Tag;

    /**
     * @var UnitEnum|string|null
     */
    protected static $navigationGroup = NavigationGroup::Products;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.brands.navigation.label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.brands.model.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.brands.model.plural');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->components([
            Section::make(__('admin.brands.sections.basic_information'))
                ->components([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.brands.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.brands.fields.slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(Brand::class, 'slug', ignoreRecord: true)
                        ->rules(['alpha_dash']),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.brands.fields.description'))
                        ->maxLength(1000)
                        ->rows(3),
                    Forms\Components\TextInput::make('website')
                        ->label(__('admin.brands.fields.website'))
                        ->url()
                        ->maxLength(255)
                        ->prefixIcon('heroicon-m-globe-alt'),
                    Forms\Components\Toggle::make('is_enabled')
                        ->label(__('admin.brands.fields.is_enabled'))
                        ->default(true)
                        ->helperText(__('admin.brands.helpers.enabled')),
                ])
                ->columns(2),
            Section::make(__('admin.brands.sections.seo'))
                ->components([
                    Forms\Components\TextInput::make('seo_title')
                        ->label(__('admin.brands.fields.seo_title'))
                        ->maxLength(60)
                        ->helperText(__('admin.brands.helpers.seo_title')),
                    Forms\Components\Textarea::make('seo_description')
                        ->label(__('admin.brands.fields.seo_description'))
                        ->maxLength(160)
                        ->rows(3)
                        ->helperText(__('admin.brands.helpers.seo_description')),
                ])
                ->columns(1)
                ->collapsible(),
            Section::make(__('admin.brands.sections.translations'))
                ->components([
                    Forms\Components\Repeater::make('translations')
                        ->label(__('admin.brands.fields.translations'))
                        ->relationship('translations')
                        ->schema([
                            Forms\Components\Select::make('locale')
                                ->label(__('admin.brands.fields.locale'))
                                ->options([
                                    'lt' => 'Lietuvių',
                                    'en' => 'English',
                                    'de' => 'Deutsch',
                                ])
                                ->required()
                                ->searchable(),
                            Forms\Components\TextInput::make('name')
                                ->label(__('admin.brands.fields.name'))
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('description')
                                ->label(__('admin.brands.fields.description'))
                                ->maxLength(1000)
                                ->rows(3),
                            Forms\Components\TextInput::make('seo_title')
                                ->label(__('admin.brands.fields.seo_title'))
                                ->maxLength(60),
                            Forms\Components\Textarea::make('seo_description')
                                ->label(__('admin.brands.fields.seo_description'))
                                ->maxLength(160)
                                ->rows(3),
                        ])
                        ->columns(2)
                        ->addActionLabel(__('admin.brands.actions.add_translation'))
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.brands.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record): string => $record->slug ?? '')
                    ->formatStateUsing(fn ($record): string => $record->trans('name') ?: $record->name),
                Tables\Columns\TextColumn::make('translations_count')
                    ->label(__('admin.brands.fields.translations_count'))
                    ->counts('translations')
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state): string => $state.' '.__('admin.brands.fields.translations')),
                Tables\Columns\TextColumn::make('website')
                    ->label(__('admin.brands.fields.website'))
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->icon('heroicon-m-globe-alt')
                    ->placeholder(__('admin.brands.placeholders.no_website')),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.brands.fields.is_enabled'))
                    ->boolean()
                    ->sortable()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('admin.brands.fields.products_count'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.brands.fields.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.brands.fields.updated_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('enabled')
                    ->label(__('admin.brands.filters.enabled_only'))
                    ->query(fn ($query) => $query->where('is_enabled', true))
                    ->toggle(),
                Tables\Filters\Filter::make('has_products')
                    ->label(__('admin.brands.filters.has_products'))
                    ->query(fn ($query) => $query->has('products'))
                    ->toggle(),
                Tables\Filters\Filter::make('has_translations')
                    ->label(__('admin.brands.filters.has_translations'))
                    ->query(fn ($query) => $query->has('translations'))
                    ->toggle(),
                Tables\Filters\SelectFilter::make('translation_locale')
                    ->label(__('admin.brands.filters.translation_locale'))
                    ->options([
                        'lt' => 'Lietuvių',
                        'en' => 'English',
                        'de' => 'Deutsch',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $locale): Builder => $query->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
                        );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_status')
                    ->label(fn ($record) => $record->is_enabled ? __('admin.brands.actions.disable') : __('admin.brands.actions.enable'))
                    ->icon(fn ($record) => $record->is_enabled ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record) => $record->is_enabled ? 'warning' : 'success')
                    ->action(fn ($record) => $record->update(['is_enabled' => ! $record->is_enabled]))
                    ->requiresConfirmation(),
                Action::make('manage_translations')
                    ->label(__('admin.brands.actions.manage_translations'))
                    ->icon('heroicon-o-language')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.brands.edit', ['record' => $record, 'activeTab' => 'translations'])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label(__('admin.brands.actions.enable_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->requiresConfirmation(),
                    BulkAction::make('disable')
                        ->label(__('admin.brands.actions.disable_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])->label(__('admin.brands.actions.bulk_actions')),
            ])
            ->defaultSort('name', 'asc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\BrandStatsWidget::class,
            Widgets\BrandOverviewWidget::class,
            Widgets\BrandPerformanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'view' => Pages\ViewBrand::route('/{record}'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // Authorization methods
    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('administrator') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view brands') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create brands') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update brands') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete brands') ?? false;
    }
}
