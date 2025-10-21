<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\CampaignProductTarget;
use App\Models\Scopes\ActiveScope;
use BackedEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
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
use Illuminate\Support\Collection;

/**
 * CampaignProductTargetResource renders the marketing targeting controls in Filament.
 */
final class CampaignProductTargetResource extends Resource
{
    /**
     * @var class-string<\Illuminate\Database\Eloquent\Model> The backing Eloquent model.
     */
    protected static ?string $model = CampaignProductTarget::class;

    /** @var string|BackedEnum|null Provide a marketing-focused icon for the navigation menu. */
    protected static $navigationIcon = 'heroicon-o-bullseye';

    /**
     * Define the navigation group to keep marketing tooling together.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('campaign_product_targets.navigation_group');
    }

    /**
     * Provide the navigation label so Lithuanian/English admins see localised text.
     */
    public static function getNavigationLabel(): string
    {
        return __('campaign_product_targets.navigation');
    }

    /**
     * Provide the plural label for the resource listing.
     */
    public static function getPluralModelLabel(): string
    {
        return __('campaign_product_targets.plural');
    }

    /**
     * Provide the singular label for record detail pages.
     */
    public static function getModelLabel(): string
    {
        return __('campaign_product_targets.single');
    }

    /**
     * Build the marketing targeting form schema.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('campaign_product_targets.sections.assignment'))
                ->description(__('campaign_product_targets.descriptions.assignment'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('campaign_product_targets.fields.campaign'))
                                ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                            Select::make('target_type')
                                ->label(__('campaign_product_targets.fields.target_type'))
                                ->options([
                                    'product' => __('campaign_product_targets.target_types.product'),
                                    'category' => __('campaign_product_targets.target_types.category'),
                                    'brand' => __('campaign_product_targets.target_types.brand'),
                                    'collection' => __('campaign_product_targets.target_types.collection'),
                                ])
                                ->required()
                                ->live()
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('campaign_product_targets.fields.product'))
                                ->relationship('product', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn (Get $get): bool => $get('target_type') === 'product')
                                ->visible(fn (Get $get): bool => $get('target_type') === 'product'),
                            Select::make('category_id')
                                ->label(__('campaign_product_targets.fields.category'))
                                ->relationship('category', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn (Get $get): bool => $get('target_type') === 'category')
                                ->visible(fn (Get $get): bool => $get('target_type') === 'category'),
                            Select::make('brand_id')
                                ->label(__('campaign_product_targets.fields.brand'))
                                ->relationship('brand', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn (Get $get): bool => $get('target_type') === 'brand')
                                ->visible(fn (Get $get): bool => $get('target_type') === 'brand'),
                            Select::make('collection_id')
                                ->label(__('campaign_product_targets.fields.collection'))
                                ->relationship('collection', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required(fn (Get $get): bool => $get('target_type') === 'collection')
                                ->visible(fn (Get $get): bool => $get('target_type') === 'collection'),
                        ])
                        ->columnSpanFull(),
                ]),
            Section::make(__('campaign_product_targets.sections.optimisation'))
                ->description(__('campaign_product_targets.descriptions.optimisation'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('priority')
                                ->label(__('campaign_product_targets.fields.priority'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(0)
                                ->helperText(__('campaign_product_targets.hints.priority')),
                            TextInput::make('weight')
                                ->label(__('campaign_product_targets.fields.weight'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText(__('campaign_product_targets.hints.weight')),
                            TextInput::make('sort_order')
                                ->label(__('campaign_product_targets.fields.sort_order'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText(__('campaign_product_targets.hints.sort_order')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('campaign_product_targets.fields.is_active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('campaign_product_targets.fields.is_featured'))
                                ->default(false),
                        ]),
                    KeyValue::make('conditions')
                        ->label(__('campaign_product_targets.fields.conditions'))
                        ->keyLabel(__('campaign_product_targets.fields.condition_key'))
                        ->valueLabel(__('campaign_product_targets.fields.condition_value'))
                        ->columnSpanFull()
                        ->helperText(__('campaign_product_targets.hints.conditions')),
                    Textarea::make('notes')
                        ->label(__('campaign_product_targets.fields.notes'))
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText(__('campaign_product_targets.hints.notes')),
                ]),
        ]);
    }

    /**
     * Configure the marketing table with campaign targeting insights.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_product_targets.columns.id'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.columns.campaign'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.columns.target_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('campaign_product_targets.target_types.' . $state))
                    ->sortable(),
                TextColumn::make('target_name')
                    ->label(__('campaign_product_targets.columns.target_name'))
                    ->searchable([
                        'product.name',
                        'category.name',
                        'brand.name',
                        'collection.name',
                    ])
                    ->wrap(),
                TextColumn::make('priority')
                    ->label(__('campaign_product_targets.columns.priority'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->label(__('campaign_product_targets.columns.weight'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.columns.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('campaign_product_targets.columns.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('campaign_product_targets.columns.created_at'))
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('campaign_product_targets.columns.updated_at'))
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_product_targets.filters.campaign'))
                    ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('target_type')
                    ->label(__('campaign_product_targets.filters.target_type'))
                    ->options([
                        'product' => __('campaign_product_targets.target_types.product'),
                        'category' => __('campaign_product_targets.target_types.category'),
                        'brand' => __('campaign_product_targets.target_types.brand'),
                        'collection' => __('campaign_product_targets.target_types.collection'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_product_targets.filters.is_active'))
                    ->boolean(),
                TernaryFilter::make('is_featured')
                    ->label(__('campaign_product_targets.filters.is_featured'))
                    ->boolean(),
                Filter::make('high_priority')
                    ->label(__('campaign_product_targets.filters.high_priority'))
                    ->query(fn (Builder $query): Builder => $query->where('priority', '>=', 80))
                    ->toggle(),
                Filter::make('recent')
                    ->label(__('campaign_product_targets.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7)))
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('campaign_product_targets.bulk.activate'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            // Ensure all selected targets are set active in one go.
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.activated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('campaign_product_targets.bulk.deactivate'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            // Ensure all selected targets are disabled quickly.
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.deactivated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('campaign_product_targets.bulk.feature'))
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            // Promote selections to featured content.
                            $records->each->update(['is_featured' => true]);

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.featured'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unfeature')
                        ->label(__('campaign_product_targets.bulk.unfeature'))
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            // Remove feature status from the chosen targets.
                            $records->each->update(['is_featured' => false]);

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.unfeatured'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('priority', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    /**
     * Configure the infolist for the dedicated view page.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema->schema([
            InfolistSection::make(__('campaign_product_targets.sections.assignment'))
                ->schema([
                    TextEntry::make('campaign.name')
                        ->label(__('campaign_product_targets.columns.campaign')),
                    TextEntry::make('target_type')
                        ->label(__('campaign_product_targets.columns.target_type'))
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => __('campaign_product_targets.target_types.' . $state)),
                    TextEntry::make('target_name')
                        ->label(__('campaign_product_targets.columns.target_name')),
                    TextEntry::make('target_identifier')
                        ->label(__('campaign_product_targets.columns.target_identifier'))
                        ->placeholder(__('campaign_product_targets.placeholders.no_identifier')),
                ])
                ->columns(2),
            InfolistSection::make(__('campaign_product_targets.sections.optimisation'))
                ->schema([
                    TextEntry::make('priority')
                        ->label(__('campaign_product_targets.columns.priority')),
                    TextEntry::make('weight')
                        ->label(__('campaign_product_targets.columns.weight')),
                    TextEntry::make('sort_order')
                        ->label(__('campaign_product_targets.columns.sort_order')),
                    IconEntry::make('is_active')
                        ->label(__('campaign_product_targets.columns.is_active'))
                        ->boolean(),
                    IconEntry::make('is_featured')
                        ->label(__('campaign_product_targets.columns.is_featured'))
                        ->boolean(),
                    KeyValueEntry::make('conditions')
                        ->label(__('campaign_product_targets.fields.conditions'))
                        ->placeholder(__('campaign_product_targets.placeholders.no_conditions')),
                    TextEntry::make('notes')
                        ->label(__('campaign_product_targets.fields.notes'))
                        ->placeholder(__('campaign_product_targets.placeholders.no_notes')),
                ])
                ->columns(2),
        ]);
    }

    /**
     * Provide relations for the resource; none are defined yet to keep focus tight.
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Register Filament pages for CRUD + dedicated view support.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaignProductTargets::route('/'),
            'create' => Pages\CreateCampaignProductTarget::route('/create'),
            'view' => Pages\ViewCampaignProductTarget::route('/{record}'),
            'edit' => Pages\EditCampaignProductTarget::route('/{record}/edit'),
        ];
    }

    /**
     * Expose globally searchable attributes for quick admin lookups.
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'campaign.name',
            'product.name',
            'category.name',
            'brand.name',
            'collection.name',
            'notes',
        ];
    }

    /**
     * Show a badge with the active record count in the navigation.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = self::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * @return Builder<CampaignProductTarget> Ensure the admin view ignores the ActiveScope.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                ActiveScope::class,
            ]);
    }
}
