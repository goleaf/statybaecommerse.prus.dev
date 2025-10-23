<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Schemas;

use App\Forms\Components\Flatpickr;
use App\Models\RecommendationCache;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Support\Filament\Components\Flatpickr;

final class RecommendationCacheForm
{
    public static function configure(Form $schema): Form
    {
        return $schema
            ->schema([
                Section::make(__('admin.recommendation_caches.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cache_key')
                                    ->label(__('admin.recommendation_caches.cache_key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(RecommendationCache::class, 'cache_key', ignoreRecord: true),
                                Select::make('block_id')
                                    ->label(__('admin.recommendation_caches.block'))
                                    ->relationship('block', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('user_id')
                                    ->label(__('admin.recommendation_caches.user'))
                                    ->relationship('user', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Select::make('product_id')
                                    ->label(__('admin.recommendation_caches.product'))
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('context_type')
                                    ->label(__('admin.recommendation_caches.context_type'))
                                    ->maxLength(100),
                                TextInput::make('hit_count')
                                    ->label(__('admin.recommendation_caches.hit_count'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                        KeyValue::make('recommendations')
                            ->label(__('admin.recommendation_caches.recommendations'))
                            ->columnSpanFull()
                            ->default([]),
                        Flatpickr::makeDateTime('expires_at')
                            ->label(__('admin.recommendation_caches.expires_at'))
                            ->required()
                            ->seconds(false)
                            ->default(now()->addHours(24)),
                    ]),
            ]);
    }
}
