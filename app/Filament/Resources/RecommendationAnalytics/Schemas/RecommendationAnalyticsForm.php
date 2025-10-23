<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationAnalytics\Schemas;

use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationConfig;
use App\Models\User;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

final class RecommendationAnalyticsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make(__('recommendation_analytics.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('block_id')
                                    ->label(__('recommendation_analytics.block'))
                                    ->options(fn (): array => RecommendationBlock::pluck('name', 'id')->all())
                                    ->required()
                                    ->searchable(),
                                Select::make('config_id')
                                    ->label(__('recommendation_analytics.config'))
                                    ->options(fn (): array => RecommendationConfig::pluck('name', 'id')->all())
                                    ->required()
                                    ->searchable(),
                                Select::make('user_id')
                                    ->label(__('recommendation_analytics.user'))
                                    ->options(fn (): array => User::pluck('name', 'id')->all())
                                    ->required()
                                    ->searchable(),
                                Select::make('product_id')
                                    ->label(__('recommendation_analytics.product'))
                                    ->options(fn (): array => Product::withoutGlobalScopes()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->nullable(),
                                Select::make('action')
                                    ->label(__('recommendation_analytics.action'))
                                    ->options([
                                        'view'        => __('recommendation_analytics.actions.view'),
                                        'click'       => __('recommendation_analytics.actions.click'),
                                        'add_to_cart' => __('recommendation_analytics.actions.add_to_cart'),
                                        'purchase'    => __('recommendation_analytics.actions.purchase'),
                                    ])
                                    ->required()
                                    ->default('view'),
                                SupportFlatpickr::makeDate('date')
                                    ->label(__('recommendation_analytics.date'))
                                    ->required()
                                    ->default(now()),
                            ]),
                    ]),
                SchemaSection::make(__('recommendation_analytics.metrics'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('ctr')
                                    ->label(__('recommendation_analytics.ctr'))
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->suffix('%'),
                                TextInput::make('conversion_rate')
                                    ->label(__('recommendation_analytics.conversion_rate'))
                                    ->numeric()
                                    ->step(0.0001)
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->suffix('%'),
                            ]),
                        KeyValue::make('metrics')
                            ->label(__('recommendation_analytics.additional_metrics'))
                            ->columnSpanFull()
                            ->helperText(__('recommendation_analytics.additional_metrics_hint'))
                            ->keyLabel(__('recommendation_analytics.metric_key'))
                            ->valueLabel(__('recommendation_analytics.metric_value')),
                    ]),
            ]);
    }
}
