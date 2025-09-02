<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionResource\Pages;
use App\Models\Collection;
use App\Services\MultiLanguageTabService;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Main Collection Information (Non-translatable)
                Forms\Components\Section::make(__('translations.collection_information'))
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('max_products')
                            ->label(__('translations.max_products'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('translations.max_products_help')),
                        Forms\Components\Toggle::make('is_visible')
                            ->label(__('translations.visible'))
                            ->default(true),
                        Forms\Components\Toggle::make('is_automatic')
                            ->label(__('translations.automatic_collection'))
                            ->default(false)
                            ->live(),
                    ])
                    ->columns(2),

                // Collection Rules Section
                Forms\Components\Section::make(__('translations.collection_rules'))
                    ->schema([
                        Forms\Components\Textarea::make('rules')
                            ->label(__('translations.collection_rules'))
                            ->visible(fn(Forms\Get $get): bool => $get('is_automatic') === true)
                            ->helperText(__('translations.collection_rules_help'))
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(Forms\Get $get): bool => $get('is_automatic') === true),

                // Multilanguage Tabs for Translatable Content
                Tabs::make('collection_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'basic_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'slug' => [
                                    'type' => 'text',
                                    'label' => __('translations.slug'),
                                    'required' => true,
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.slug_auto_generated'),
                                ],
                                'description' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.description'),
                                    'toolbar' => ['bold', 'italic', 'link', 'bulletList', 'orderedList', 'h2', 'h3'],
                                ],
                            ],
                            'seo_information' => [
                                'seo_title' => [
                                    'type' => 'text',
                                    'label' => __('translations.seo_title'),
                                    'maxLength' => 255,
                                    'placeholder' => __('translations.seo_title_help'),
                                ],
                                'seo_description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.seo_description'),
                                    'maxLength' => 300,
                                    'rows' => 3,
                                    'placeholder' => __('translations.seo_description_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('collection_tab')
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'manual' => 'primary',
                        'automatic' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                    ]),
                Tables\Filters\Filter::make('enabled')
                    ->query(fn($query) => $query->where('is_enabled', true)),
                Tables\Filters\Filter::make('featured')
                    ->query(fn($query) => $query->where('is_featured', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListCollections::route('/'),
            'create' => Pages\CreateCollection::route('/create'),
            'view' => Pages\ViewCollection::route('/{record}'),
            'edit' => Pages\EditCollection::route('/{record}/edit'),
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
