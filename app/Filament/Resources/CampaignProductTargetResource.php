<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\CampaignProductTarget;
use App\Models\Scopes\ActiveScope;
use BackedEnum;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Lang;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class CampaignProductTargetResource extends Resource
{
    protected static ?string $model = CampaignProductTarget::class;

    /**
     * @var string|\BackedEnum|null Keep Filament navigation metadata flexible between enums and plain strings.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bullseye-arrow';

    protected static ?string $recordTitleAttribute = 'target_type';

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_product_targets.navigation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_product_targets.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_product_targets.single');
    }

    /**
     * Define the Campaign Product Target form with conditional selectors and marketing metadata.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('campaign_product_targets.basic_information'))
                ->description(__('campaign_product_targets.campaign_selection_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_product_targets.campaign'))
                                ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('target_type')
                                ->label(__('campaign_product_targets.target_type'))
                                ->options([
                                    'product'    => __('campaign_product_targets.types.product'),
                                    'category'   => __('campaign_product_targets.types.category'),
                                    'brand'      => __('campaign_product_targets.types.brand'),
                                    'collection' => __('campaign_product_targets.types.collection'),
                                ])
                                ->required()
                                ->live(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('campaign_product_targets.product'))
                                ->relationship('product', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('target_type') === 'product')
                                ->required(fn (Get $get): bool => $get('target_type') === 'product'),
                            Select::make('category_id')
                                ->label(__('campaign_product_targets.category'))
                                ->relationship('category', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('target_type') === 'category')
                                ->required(fn (Get $get): bool => $get('target_type') === 'category'),
                            Select::make('brand_id')
                                ->label(__('campaign_product_targets.brand'))
                                ->relationship('brand', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('target_type') === 'brand')
                                ->required(fn (Get $get): bool => $get('target_type') === 'brand'),
                            Select::make('collection_id')
                                ->label(__('campaign_product_targets.collection'))
                                ->relationship('collection', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->visible(fn (Get $get): bool => $get('target_type') === 'collection')
                                ->required(fn (Get $get): bool => $get('target_type') === 'collection'),
                        ])->columns(2),
                ]),
            Section::make(__('campaign_product_targets.targeting_rules'))
                ->description(__('campaign_product_targets.targeting_rules_description'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('priority')
                                ->label(__('campaign_product_targets.priority'))
                                ->numeric()
                                ->default(50)
                                ->minValue(0)
                                ->maxValue(100),
                            TextInput::make('weight')
                                ->label(__('campaign_product_targets.weight'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('campaign_product_targets.weight_help')),
                            TextInput::make('sort_order')
                                ->label(__('campaign_product_targets.sort_order'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('campaign_product_targets.sort_order_help')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('campaign_product_targets.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('campaign_product_targets.is_featured'))
                                ->default(false),
                        ]),
                    KeyValue::make('conditions')
                        ->label(__('campaign_product_targets.conditions'))
                        ->keyLabel(__('campaign_product_targets.conditions_key'))
                        ->valueLabel(__('campaign_product_targets.conditions_value'))
                        ->helperText(__('campaign_product_targets.conditions_help'))
                        ->columnSpanFull()
                        ->reorderable(),
                    Textarea::make('notes')
                        ->label(__('campaign_product_targets.notes'))
                        ->helperText(__('campaign_product_targets.notes_help'))
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Configure the marketing oriented table with rich filtering, search, and bulk campaign actions.
     */
    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_product_targets.id'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.campaign'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.target_type'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(function (?string $state): string {
                        if ($state === null) {
                            return __('campaign_product_targets.unknown_target');
                        }

                        $key = 'campaign_product_targets.types.' . $state;

                        return Lang::has($key) ? __($key) : (string) $state;
                    }),
                TextColumn::make('target_name')
                    ->label(__('campaign_product_targets.target_name'))
                    ->formatStateUsing(fn (?string $state, CampaignProductTarget $record): string => $state ?? match ($record->target_type) {
                        'product'    => __('campaign_product_targets.no_product'),
                        'category'   => __('campaign_product_targets.no_category'),
                        'brand'      => __('campaign_product_targets.no_brand'),
                        'collection' => __('campaign_product_targets.no_collection'),
                        default      => __('campaign_product_targets.unknown_target'),
                    })
                    ->wrap()
                    ->searchable(['product.name', 'category.name', 'brand.name', 'collection.name'])
                    ->toggleable(),
                TextColumn::make('target_identifier')
                    ->label(__('campaign_product_targets.target_sku'))
                    ->searchable(['product.sku', 'category.slug', 'brand.slug', 'collection.slug'])
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('priority')
                    ->label(__('campaign_product_targets.priority'))
                    ->sortable()
                    ->badge()
                    ->color(fn (CampaignProductTarget $record): string => $record->priority >= 80 ? 'success' : 'gray'),
                TextColumn::make('weight')
                    ->label(__('campaign_product_targets.weight'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('campaign_product_targets.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('priority', 'desc')
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_product_targets.campaign'))
                    ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                    ->preload()
                    ->searchable(),
                SelectFilter::make('target_type')
                    ->label(__('campaign_product_targets.target_type'))
                    ->options([
                        'product'    => __('campaign_product_targets.types.product'),
                        'category'   => __('campaign_product_targets.types.category'),
                        'brand'      => __('campaign_product_targets.types.brand'),
                        'collection' => __('campaign_product_targets.types.collection'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_product_targets.is_active'))
                    ->trueLabel(__('campaign_product_targets.active_only'))
                    ->falseLabel(__('campaign_product_targets.inactive_only'))
                    ->nullable(),
                TernaryFilter::make('is_featured')
                    ->label(__('campaign_product_targets.is_featured'))
                    ->trueLabel(__('campaign_product_targets.featured_only'))
                    ->falseLabel(__('campaign_product_targets.not_featured'))
                    ->nullable(),
                Filter::make('high_priority')
                    ->label(__('campaign_product_targets.high_priority'))
                    ->query(fn (Builder $query): Builder => $query->where('priority', '>=', 80)),
                Filter::make('recent')
                    ->label(__('campaign_product_targets.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', Carbon::now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('campaign_product_targets.activate'))
                        ->icon('heroicon-o-bolt')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(static fn (CampaignProductTarget $target) => $target->update(['is_active' => true]));

                            Notification::make()
                                ->title(__('campaign_product_targets.activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('campaign_product_targets.deactivate'))
                        ->icon('heroicon-o-no-symbol')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(static fn (CampaignProductTarget $target) => $target->update(['is_active' => false]));

                            Notification::make()
                                ->title(__('campaign_product_targets.deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('campaign_product_targets.feature'))
                        ->icon('heroicon-o-star')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(static fn (CampaignProductTarget $target) => $target->update(['is_featured' => true]));

                            Notification::make()
                                ->title(__('campaign_product_targets.featured_successfully'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unfeature')
                        ->label(__('campaign_product_targets.unfeature'))
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(static fn (CampaignProductTarget $target) => $target->update(['is_featured' => false]));

                            Notification::make()
                                ->title(__('campaign_product_targets.unfeatured_successfully'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make()
                        ->label(__('campaign_product_targets.delete_bulk'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->searchPlaceholder(__('campaign_product_targets.search_placeholder'));
    }

    /**
     * Provide a structured infolist for the record view page.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            InfolistSection::make(__('campaign_product_targets.view.sections.overview'))
                ->schema([
                    TextEntry::make('campaign.name')
                        ->label(__('campaign_product_targets.campaign')),
                    TextEntry::make('target_type')
                        ->label(__('campaign_product_targets.target_type'))
                        ->formatStateUsing(function (?string $state): string {
                            if ($state === null) {
                                return __('campaign_product_targets.unknown_target');
                            }

                            $key = 'campaign_product_targets.types.' . $state;

                            return Lang::has($key) ? __($key) : (string) $state;
                        }),
                    TextEntry::make('target_name')
                        ->label(__('campaign_product_targets.target_name')),
                    TextEntry::make('target_identifier')
                        ->label(__('campaign_product_targets.target_sku')),
                ]),
            InfolistSection::make(__('campaign_product_targets.view.sections.status'))
                ->columns(2)
                ->schema([
                    IconEntry::make('is_active')
                        ->label(__('campaign_product_targets.is_active'))
                        ->boolean(),
                    IconEntry::make('is_featured')
                        ->label(__('campaign_product_targets.is_featured'))
                        ->boolean(),
                    TextEntry::make('priority')
                        ->label(__('campaign_product_targets.priority')),
                    TextEntry::make('weight')
                        ->label(__('campaign_product_targets.weight')),
                    TextEntry::make('sort_order')
                        ->label(__('campaign_product_targets.sort_order')),
                ]),
            InfolistSection::make(__('campaign_product_targets.conditions'))
                ->schema([
                    TextEntry::make('conditions')
                        ->label(__('campaign_product_targets.conditions'))
                        ->formatStateUsing(static fn ($state): string => is_array($state)
                            ? collect($state)->map(fn ($value, $key) => $key . ': ' . (is_scalar($value) ? (string) $value : json_encode($value)))->implode(PHP_EOL)
                            : (string) ($state ?? ''))
                        ->default(__('campaign_product_targets.no_conditions')),
                    TextEntry::make('notes')
                        ->label(__('campaign_product_targets.notes'))
                        ->default(__('campaign_product_targets.no_notes')),
                    TextEntry::make('created_at')
                        ->label(__('campaign_product_targets.created_at'))
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->label(__('campaign_product_targets.updated_at'))
                        ->dateTime(),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCampaignProductTargets::route('/'),
            'create' => Pages\CreateCampaignProductTarget::route('/create'),
            'view'   => Pages\ViewCampaignProductTarget::route('/{record}'),
            'edit'   => Pages\EditCampaignProductTarget::route('/{record}/edit'),
        ];
    }

    /**
     * Ensure marketing managers can audit both active and inactive targets.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
        ]);
    }
}