<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationConfigResource\Pages;
use App\Models\RecommendationConfig;
use App\Support\Concerns\HasNav;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Novadaemon\FilamentCombobox\Combobox;
use UnitEnum;

final class RecommendationConfigResource extends Resource
{
    use HasNav;

    protected static ?string $model = RecommendationConfig::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 11;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('recommendation_configs.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('recommendation_configs.plural');
    }

    public static function getModelLabel(): string
    {
        return __('recommendation_configs.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('recommendation_config.sections.basic_info'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('recommendation_config.fields.name'))
                                ->required()
                                ->maxLength(255)
                                // Guard against duplicate configuration names so the Livewire test never trips a database
                                // unique constraint when attempting to create an existing record.
                                ->unique(RecommendationConfig::class, 'name', ignoreRecord: true),
                            Select::make('type')
                                ->label(__('recommendation_config.fields.type'))
                                ->options([
                                    'collaborative' => 'collaborative',
                                    'content_based' => 'content_based',
                                    'hybrid'        => 'hybrid',
                                    'popularity'    => 'popularity',
                                    'trending'      => 'trending',
                                    'cross_sell'    => 'cross_sell',
                                    'up_sell'       => 'up_sell',
                                ])
                                ->required()
                                ->native(false),
                        ]),
                    Textarea::make('description')
                        ->label(__('recommendation_config.fields.description')),
                ]),
            SchemaSection::make(__('recommendation_config.sections.parameters'))
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            TextInput::make('min_score')
                                ->label(__('recommendation_config.fields.min_score'))
                                ->numeric()
                                // Mirror the database default so blank create forms hydrate deterministic values.
                                ->default(0.1),
                            TextInput::make('max_results')
                                ->label(__('recommendation_config.fields.max_results'))
                                ->numeric()
                                // Keep max results aligned with schema defaults to avoid null assignments during creation.
                                ->default(10),
                            TextInput::make('decay_factor')
                                ->label(__('recommendation_config.fields.decay_factor'))
                                ->numeric()
                                // Provide a sensible baseline decay factor while still allowing operators to fine-tune it.
                                ->default(0.9),
                            TextInput::make('priority')
                                ->label(__('recommendation_config.fields.priority'))
                                ->numeric()
                                // Prevent null persistence on the NOT NULL column by defaulting to zero priority ordering.
                                ->default(0),
                            TextInput::make('cache_ttl')
                                ->label(__('recommendation_config.fields.cache_ttl'))
                                ->numeric()
                                // Respect the 3600 second cache default defined at the database layer.
                                ->default(3600),
                            TextInput::make('sort_order')
                                ->label(__('recommendation_config.fields.sort_order'))
                                ->numeric()
                                // Ensure sort order stays deterministic without forcing the user to enter a value manually.
                                ->default(0),
                        ]),
                ]),
            SchemaSection::make(__('recommendation_config.sections.flags'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('recommendation_config.fields.is_active')),
                            Toggle::make('is_default')
                                ->label(__('recommendation_config.fields.is_default')),
                        ]),
                ]),
            SchemaSection::make(__('recommendation_config.sections.relationships'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Combobox::make('products')
                                ->label(__('recommendation_config.fields.products'))
                                ->relationship('products', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->formatStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state)
                                ->dehydrateStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state),
                            Combobox::make('categories')
                                ->label(__('recommendation_config.fields.categories'))
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->formatStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state)
                                ->dehydrateStateUsing(fn ($state) => is_array($state) ? array_values(collect($state)->sort()->all()) : $state),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        // Pre-build bulk action definitions so they can be referenced consistently below and keep table configuration tidy.
        $activateAction = BulkAction::make('activate')
            ->label(__('recommendation_config.actions.activate_selected'))
            ->icon('heroicon-m-bolt')
            ->color('success')
            // Apply the activation flag to every selected record so the table bulk action mirrors the tests' expectations.
            ->action(static function (Collection $records): void {
                foreach ($records as $record) {
                    if (! $record instanceof RecommendationConfig) {
                        // Skip unexpected payloads so bulk activation never crashes when the collection is re-typed elsewhere.
                        continue;
                    }

                    $record->forceFill(['is_active' => true])->save();
                }
            })
            ->deselectRecordsAfterCompletion();

        $deactivateAction = BulkAction::make('deactivate')
            ->label(__('recommendation_config.actions.deactivate_selected'))
            ->icon('heroicon-m-pause')
            ->color('warning')
            // Flip the activation flag off for each selected record while leaving other attributes untouched.
            ->action(static function (Collection $records): void {
                foreach ($records as $record) {
                    if (! $record instanceof RecommendationConfig) {
                        // Mirror the guardrail from the activation bulk action to keep type-safety consistent.
                        continue;
                    }

                    $record->forceFill(['is_active' => false])->save();
                }
            })
            ->deselectRecordsAfterCompletion();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('recommendation_config.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('recommendation_config.fields.type'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('recommendation_config.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('recommendation_config.fields.is_default'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('recommendation_config.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('recommendation_config.fields.type'))
                    ->options([
                        'collaborative' => 'collaborative',
                        'content_based' => 'content_based',
                        'hybrid'        => 'hybrid',
                        'popularity'    => 'popularity',
                        'trending'      => 'trending',
                        'cross_sell'    => 'cross_sell',
                        'up_sell'       => 'up_sell',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('recommendation_config.fields.is_active')),
                TernaryFilter::make('is_default')
                    ->label(__('recommendation_config.fields.is_default')),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                $activateAction,
                $deactivateAction,
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRecommendationConfigs::route('/'),
            'create' => Pages\CreateRecommendationConfig::route('/create'),
            'view'   => Pages\ViewRecommendationConfig::route('/{record}'),
            'edit'   => Pages\EditRecommendationConfig::route('/{record}/edit'),
        ];
    }
}
