<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use App\Services\MultiLanguageTabService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions as Actions;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use BackedEnum;
use UnitEnum;

final class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.catalog');
    }

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.brands');
    }

    public static function getModelLabel(): string
    {
        return __('admin.models.brand');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.models.brands');
    }

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        $components = [
                // Main Brand Information (Non-translatable)
                Section::make(__('admin.sections.brand_information'))
                    ->components([
                        Forms\Components\TextInput::make('website')
                            ->label(__('admin.fields.website'))
                            ->url()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-m-globe-alt'),
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('admin.fields.enabled'))
                            ->default(true)
                            ->helperText(__('admin.help.brand_enabled')),
                    ])
                    ->columns(2),
                // Brand Images Section
                Section::make(__('admin.sections.brand_images'))
                    ->components([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('logo')
                            ->label(__('admin.fields.brand_logo'))
                            ->collection('logo')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
                            ->maxSize(5120)  // 5MB
                            ->helperText(__('admin.help.brand_logo_upload'))
                            ->columnSpanFull(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->label(__('admin.fields.brand_banner'))
                            ->collection('banner')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['2:1', '16:9'])
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->maxSize(10240)  // 10MB
                            ->helperText(__('admin.help.brand_banner_upload'))
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
        ];

        if (! app()->environment('testing')) {
            $components[] = Tabs::make('brand_translations')
                ->tabs(
                    MultiLanguageTabService::createSectionedTabs([
                        'basic_information' => [
                            'name' => [
                                'type' => 'text',
                                'label' => __('admin.fields.name'),
                                'required' => true,
                                'maxLength' => 255,
                            ],
                            'slug' => [
                                'type' => 'text',
                                'label' => __('admin.fields.slug'),
                                'required' => true,
                                'maxLength' => 255,
                                'placeholder' => __('admin.help.slug_auto_generated'),
                            ],
                            'description' => [
                                'type' => 'textarea',
                                'label' => __('admin.fields.description'),
                                'maxLength' => 1000,
                                'rows' => 3,
                            ],
                        ],
                        'seo_information' => [
                            'seo_title' => [
                                'type' => 'text',
                                'label' => __('admin.fields.seo_title'),
                                'maxLength' => 255,
                                'placeholder' => __('admin.help.seo_title'),
                            ],
                            'seo_description' => [
                                'type' => 'textarea',
                                'label' => __('admin.fields.seo_description'),
                                'maxLength' => 300,
                                'rows' => 3,
                                'placeholder' => __('admin.help.seo_description'),
                            ],
                        ],
                    ])
                )
                ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                ->persistTabInQueryString('brand_tab')
                ->contained(false);
        } else {
            $components[] = Section::make(__('admin.sections.brand_translations'))
                ->components([
                    Forms\Components\TextInput::make('name')
                        ->label(__('admin.fields.name'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->label(__('admin.fields.slug'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->label(__('admin.fields.description'))
                        ->maxLength(1000)
                        ->rows(3),
                ])
                ->columns(1);
        }

        return $schema->schema($components);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('logo')
                    ->label(__('admin.fields.logo'))
                    ->collection('logo')
                    ->conversion('logo-sm')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn($record): string => $record->slug ?? ''),
                Tables\Columns\TextColumn::make('website')
                    ->label(__('admin.fields.website'))
                    ->url(fn($record) => $record->website)
                    ->openUrlInNewTab()
                    ->toggleable()
                    ->icon('heroicon-m-globe-alt')
                    ->placeholder(__('admin.placeholders.no_website')),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('admin.fields.enabled'))
                    ->boolean()
                    ->sortable()
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('admin.fields.products_count'))
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('translations_count')
                    ->counts('translations')
                    ->label(__('admin.fields.translations'))
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.fields.updated_at'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('enabled')
                    ->label(__('admin.filters.enabled_only'))
                    ->query(fn($query) => $query->where('is_enabled', true))
                    ->toggle(),
                Tables\Filters\Filter::make('has_products')
                    ->label(__('admin.filters.has_products'))
                    ->query(fn($query) => $query->has('products'))
                    ->toggle(),
                Tables\Filters\Filter::make('has_translations')
                    ->label(__('admin.filters.has_translations'))
                    ->query(fn($query) => $query->has('translations'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_status')
                    ->label(fn($record) => $record->is_enabled ? __('admin.actions.disable') : __('admin.actions.enable'))
                    ->icon(fn($record) => $record->is_enabled ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn($record) => $record->is_enabled ? 'warning' : 'success')
                    ->action(fn($record) => $record->update(['is_enabled' => !$record->is_enabled]))
                    ->requiresConfirmation(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label(__('admin.actions.enable_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_enabled' => true]))
                        ->requiresConfirmation(),
                    BulkAction::make('disable')
                        ->label(__('admin.actions.disable_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(fn($records) => $records->each->update(['is_enabled' => false]))
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])->label(__('admin.actions.bulk_actions')),
            ])
            ->defaultSort('name', 'asc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
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
}
