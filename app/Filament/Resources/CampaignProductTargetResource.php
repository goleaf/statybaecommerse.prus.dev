<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\Campaign;
use App\Models\CampaignProductTarget;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CampaignProductTargetResource extends Resource
{
    protected static ?string $model = CampaignProductTarget::class;

    // protected static $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function getModelLabel(): string
    {
        return __('campaign_product_targets.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_product_targets.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_product_targets.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('campaign_product_targets.tabs'))
                    ->tabs([
                        Tab::make(__('campaign_product_targets.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Select::make('campaign_id')
                                    ->label(__('campaign_product_targets.campaign'))
                                    ->relationship('campaign', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('target_type')
                                    ->label(__('campaign_product_targets.target_type'))
                                    ->options([
                                        'product' => __('campaign_product_targets.types.product'),
                                        'category' => __('campaign_product_targets.types.category'),
                                        'brand' => __('campaign_product_targets.types.brand'),
                                        'collection' => __('campaign_product_targets.types.collection'),
                                    ])
                                    ->required()
                                    ->live()
                                    ->native(false),
                                Select::make('product_id')
                                    ->label(__('campaign_product_targets.product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn(callable $get) => $get('target_type') === 'product'),
                                Select::make('category_id')
                                    ->label(__('campaign_product_targets.category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn(callable $get) => $get('target_type') === 'category'),
                            ]),
                        Tab::make(__('campaign_product_targets.settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('campaign_product_targets.is_active'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.campaign'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.target_type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product' => 'success',
                        'category' => 'info',
                        'brand' => 'warning',
                        'collection' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('product.name')
                    ->label(__('campaign_product_targets.product'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn($record) => $record?->target_type === 'product'),
                TextColumn::make('category.name')
                    ->label(__('campaign_product_targets.category'))
                    ->searchable()
                    ->sortable()
                    ->visible(fn($record) => $record?->target_type === 'category'),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('campaign_product_targets.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('campaign_product_targets.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_product_targets.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('target_type')
                    ->label(__('campaign_product_targets.target_type'))
                    ->options([
                        'product' => __('campaign_product_targets.types.product'),
                        'category' => __('campaign_product_targets.types.category'),
                        'brand' => __('campaign_product_targets.types.brand'),
                        'collection' => __('campaign_product_targets.types.collection'),
                    ]),
                SelectFilter::make('product_id')
                    ->label(__('campaign_product_targets.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label(__('campaign_product_targets.category'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_product_targets.is_active'))
                    ->placeholder(__('campaign_product_targets.all_records'))
                    ->trueLabel(__('campaign_product_targets.active_only'))
                    ->falseLabel(__('campaign_product_targets.inactive_only')),
            ])
            ->actions([
                // Actions will be handled by pages
            ])
            ->bulkActions([
                // Bulk actions will be handled by pages
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
            'index' => Pages\ListCampaignProductTargets::route('/'),
            'create' => Pages\CreateCampaignProductTarget::route('/create'),
            'edit' => Pages\EditCampaignProductTarget::route('/{record}/edit'),
        ];
    }
}
