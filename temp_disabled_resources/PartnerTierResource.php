<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerTierResource\Pages;
use App\Models\PartnerTier;
use App\Services\MultiLanguageTabService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;
use BackedEnum;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class PartnerTierResource extends Resource
{
    protected static ?string $model = PartnerTier::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-star';

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return __('admin.partner_tier.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.partner_tier.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Partner Tier Settings (Non-translatable)
                Forms\Components\Section::make(__('translations.partner_tier_settings'))
                    ->components([
                        
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label(__('admin.partner_tier.form.discount_percentage'))
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->helperText(__('admin.partner_tier.form.discount_help')),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('admin.partner_tier.form.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('admin.partner_tier.form.sort_order_help')),
                        
                        Forms\Components\Toggle::make('active')
                            ->label(__('admin.partner_tier.form.active'))
                            ->default(true),
                    ])
                    ->columns(2),

                // Multilanguage Tabs for Partner Tier Content
                Tabs::make('partner_tier_translations')
                    ->tabs(
                        MultiLanguageTabService::createSectionedTabs([
                            'tier_information' => [
                                'name' => [
                                    'type' => 'text',
                                    'label' => __('translations.name'),
                                    'required' => true,
                                    'maxLength' => 255,
                                ],
                                'description' => [
                                    'type' => 'textarea',
                                    'label' => __('translations.description'),
                                    'maxLength' => 1000,
                                    'rows' => 3,
                                    'placeholder' => __('translations.tier_description_help'),
                                ],
                                'benefits' => [
                                    'type' => 'rich_editor',
                                    'label' => __('translations.benefits'),
                                    'toolbar' => ['bold', 'italic', 'link', 'bulletList', 'orderedList'],
                                    'placeholder' => __('translations.tier_benefits_help'),
                                ],
                            ],
                        ])
                    )
                    ->activeTab(MultiLanguageTabService::getDefaultActiveTab())
                    ->persistTabInQueryString('partner_tier_tab')
                    ->contained(false),
                
                Forms\Components\Section::make(__('admin.partner_tier.form.requirements'))
                    ->components([
                        Forms\Components\TextInput::make('minimum_order_value')
                            ->label(__('admin.partner_tier.form.minimum_order_value'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText(__('admin.partner_tier.form.minimum_order_help')),
                        
                        Forms\Components\TextInput::make('minimum_annual_volume')
                            ->label(__('admin.partner_tier.form.minimum_annual_volume'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText(__('admin.partner_tier.form.annual_volume_help')),
                        
                        Forms\Components\Textarea::make('benefits')
                            ->label(__('admin.partner_tier.form.benefits'))
                            ->maxLength(2000)
                            ->rows(4)
                            ->helperText(__('admin.partner_tier.form.benefits_help')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.partner_tier.table.name'))
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('admin.partner_tier.table.discount_percentage'))
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('minimum_order_value')
                    ->label(__('admin.partner_tier.table.minimum_order'))
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('minimum_annual_volume')
                    ->label(__('admin.partner_tier.table.annual_volume'))
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('partners_count')
                    ->label(__('admin.partner_tier.table.partners_count'))
                    ->counts('partners')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.partner_tier.table.sort_order'))
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('active')
                    ->label(__('admin.partner_tier.table.active'))
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.partner_tier.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('admin.partner_tier.filters.active')),
                
                Tables\Filters\Filter::make('has_partners')
                    ->label(__('admin.partner_tier.filters.has_partners'))
                    ->query(fn (Builder $query): Builder => $query->has('partners')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListPartnerTiers::route('/'),
            'create' => Pages\CreatePartnerTier::route('/create'),
            'view' => Pages\ViewPartnerTier::route('/{record}'),
            'edit' => Pages\EditPartnerTier::route('/{record}/edit'),
        ];
    }
}
