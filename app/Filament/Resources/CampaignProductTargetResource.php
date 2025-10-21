<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\CampaignProductTarget;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry as InfolistKeyValueEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;

/**
 * CampaignProductTargetResource
 *
 * Provides a comprehensive Filament admin resource for managing campaign product targeting rules.
 */
final class CampaignProductTargetResource extends Resource
{
    /**
     * The Eloquent model backing the resource.
     */
    protected static ?string $model = CampaignProductTarget::class;

    /**
     * @var string|\BackedEnum|null Define the marketing navigation icon without introducing typed properties.
     */
    protected static $navigationIcon = 'heroicon-o-bullseye';

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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('campaign_product_targets.sections.assignment'))
                    ->description(__('campaign_product_targets.sections.assignment_help'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('campaign_id')
                                    ->label(__('campaign_product_targets.fields.campaign'))
                                    ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText(__('campaign_product_targets.help.campaign')),
                                Select::make('target_type')
                                    ->label(__('campaign_product_targets.fields.target_type'))
                                    ->options([
                                        'product' => __('campaign_product_targets.types.product'),
                                        'category' => __('campaign_product_targets.types.category'),
                                        'brand' => __('campaign_product_targets.types.brand'),
                                        'collection' => __('campaign_product_targets.types.collection'),
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->helperText(__('campaign_product_targets.help.target_type'))
                                    ->afterStateUpdated(static function (?string $state, callable $set): void {
                                        // Reset dependent selectors when the target type changes so we do not persist stale IDs.
                                        if ($state === null) {
                                            return;
                                        }

                                        $set('product_id', null);
                                        $set('category_id', null);
                                        $set('brand_id', null);
                                        $set('collection_id', null);
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('campaign_product_targets.fields.product'))
                                    ->relationship('product', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get): bool => $get('target_type') === 'product')
                                    ->required(fn (Get $get): bool => $get('target_type') === 'product')
                                    ->helperText(__('campaign_product_targets.help.product')),
                                Select::make('category_id')
                                    ->label(__('campaign_product_targets.fields.category'))
                                    ->relationship('category', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get): bool => $get('target_type') === 'category')
                                    ->required(fn (Get $get): bool => $get('target_type') === 'category')
                                    ->helperText(__('campaign_product_targets.help.category')),
                                Select::make('brand_id')
                                    ->label(__('campaign_product_targets.fields.brand'))
                                    ->relationship('brand', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get): bool => $get('target_type') === 'brand')
                                    ->required(fn (Get $get): bool => $get('target_type') === 'brand')
                                    ->helperText(__('campaign_product_targets.help.brand')),
                                Select::make('collection_id')
                                    ->label(__('campaign_product_targets.fields.collection'))
                                    ->relationship('collection', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get): bool => $get('target_type') === 'collection')
                                    ->required(fn (Get $get): bool => $get('target_type') === 'collection')
                                    ->helperText(__('campaign_product_targets.help.collection')),
                            ])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('campaign_product_targets.sections.behaviour'))
                    ->description(__('campaign_product_targets.sections.behaviour_help'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('priority')
                                    ->label(__('campaign_product_targets.fields.priority'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText(__('campaign_product_targets.help.priority')),
                                TextInput::make('weight')
                                    ->label(__('campaign_product_targets.fields.weight'))
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0)
                                    ->helperText(__('campaign_product_targets.help.weight')),
                                TextInput::make('sort_order')
                                    ->label(__('campaign_product_targets.fields.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText(__('campaign_product_targets.help.sort_order')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('campaign_product_targets.fields.is_active'))
                                    ->default(true)
                                    ->helperText(__('campaign_product_targets.help.is_active')),
                                Toggle::make('is_featured')
                                    ->label(__('campaign_product_targets.fields.is_featured'))
                                    ->default(false)
                                    ->helperText(__('campaign_product_targets.help.is_featured')),
                            ]),
                    ]),
                Section::make(__('campaign_product_targets.sections.metadata'))
                    ->description(__('campaign_product_targets.sections.metadata_help'))
                    ->schema([
                        KeyValue::make('conditions')
                            ->label(__('campaign_product_targets.fields.conditions'))
                            ->keyLabel(__('campaign_product_targets.fields.condition_key'))
                            ->valueLabel(__('campaign_product_targets.fields.condition_value'))
                            ->addButtonLabel(__('campaign_product_targets.actions.add_condition'))
                            ->columnSpanFull()
                            ->helperText(__('campaign_product_targets.help.conditions')),
                        Textarea::make('notes')
                            ->label(__('campaign_product_targets.fields.notes'))
                            ->rows(4)
                            ->columnSpanFull()
                            ->helperText(__('campaign_product_targets.help.notes')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_product_targets.columns.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.columns.campaign'))
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.columns.target_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('campaign_product_targets.types.' . $state))
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'success',
                        'category' => 'warning',
                        'brand' => 'info',
                        'collection' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('target_name')
                    ->label(__('campaign_product_targets.columns.target_name'))
                    ->wrap()
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        // Allow the global table search to match names across related models.
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->whereHas('product', static function (Builder $productQuery) use ($search): void {
                                    $productQuery->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('category', static function (Builder $categoryQuery) use ($search): void {
                                    $categoryQuery->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('brand', static function (Builder $brandQuery) use ($search): void {
                                    $brandQuery->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('collection', static function (Builder $collectionQuery) use ($search): void {
                                    $collectionQuery->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('campaign', static function (Builder $campaignQuery) use ($search): void {
                                    $campaignQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                    }),
                TextColumn::make('priority')
                    ->label(__('campaign_product_targets.columns.priority'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.columns.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label(__('campaign_product_targets.columns.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('campaign_product_targets.columns.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority', 'desc')
            ->recordUrl(fn (CampaignProductTarget $record): string => static::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_product_targets.filters.campaign'))
                    ->relationship('campaign', 'name', fn (Builder $query) => $query->withoutGlobalScopes())
                    ->searchable()
                    ->indicator(__('campaign_product_targets.filters.campaign_indicator')),
                SelectFilter::make('target_type')
                    ->label(__('campaign_product_targets.filters.target_type'))
                    ->options([
                        'product' => __('campaign_product_targets.types.product'),
                        'category' => __('campaign_product_targets.types.category'),
                        'brand' => __('campaign_product_targets.types.brand'),
                        'collection' => __('campaign_product_targets.types.collection'),
                    ])
                    ->indicator(__('campaign_product_targets.filters.target_type_indicator')),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_product_targets.filters.is_active'))
                    ->indicator(__('campaign_product_targets.filters.is_active_indicator')),
                Filter::make('high_priority')
                    ->label(__('campaign_product_targets.filters.high_priority'))
                    ->query(fn (Builder $query): Builder => $query->where('priority', '>=', 80))
                    ->toggle(),
                Filter::make('recent')
                    ->label(__('campaign_product_targets.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', Carbon::now()->subDays(7)))
                    ->toggle(),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('campaign_product_targets.actions.view')),
                EditAction::make()
                    ->label(__('campaign_product_targets.actions.edit')),
                DeleteAction::make()
                    ->label(__('campaign_product_targets.actions.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('campaign_product_targets.bulk.activate'))
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(static function (EloquentCollection $records): void {
                            // Activate each selected target atomically.
                            $records->each(static function (CampaignProductTarget $record): void {
                                $record->update(['is_active' => true]);
                            });

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.activated_title'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label(__('campaign_product_targets.bulk.deactivate'))
                        ->icon('heroicon-o-pause-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(static function (EloquentCollection $records): void {
                            // Deactivate each selected target atomically.
                            $records->each(static function (CampaignProductTarget $record): void {
                                $record->update(['is_active' => false]);
                            });

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.deactivated_title'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('feature')
                        ->label(__('campaign_product_targets.bulk.feature'))
                        ->icon('heroicon-o-star')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(static function (EloquentCollection $records): void {
                            // Mark each selected target as featured.
                            $records->each(static function (CampaignProductTarget $record): void {
                                $record->update(['is_featured' => true]);
                            });

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.featured_title'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('unfeature')
                        ->label(__('campaign_product_targets.bulk.unfeature'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(static function (EloquentCollection $records): void {
                            // Remove the featured flag from each selected target.
                            $records->each(static function (CampaignProductTarget $record): void {
                                $record->update(['is_featured' => false]);
                            });

                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.unfeatured_title'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->label(__('campaign_product_targets.bulk.delete')),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make(__('campaign_product_targets.sections.assignment'))
                    ->schema([
                        InfolistGrid::make(2)
                            ->schema([
                                TextEntry::make('campaign.name')
                                    ->label(__('campaign_product_targets.columns.campaign')),
                                TextEntry::make('target_type')
                                    ->label(__('campaign_product_targets.columns.target_type'))
                                    ->formatStateUsing(fn (string $state): string => __('campaign_product_targets.types.' . $state)),
                                TextEntry::make('target_name')
                                    ->label(__('campaign_product_targets.columns.target_name'))
                                    ->columnSpanFull(),
                                TextEntry::make('target_identifier')
                                    ->label(__('campaign_product_targets.columns.target_identifier'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
                InfolistSection::make(__('campaign_product_targets.sections.behaviour'))
                    ->schema([
                        InfolistGrid::make(3)
                            ->schema([
                                TextEntry::make('priority')
                                    ->label(__('campaign_product_targets.columns.priority')),
                                TextEntry::make('weight')
                                    ->label(__('campaign_product_targets.columns.weight')),
                                TextEntry::make('sort_order')
                                    ->label(__('campaign_product_targets.columns.sort_order')),
                            ]),
                        InfolistGrid::make(2)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label(__('campaign_product_targets.columns.is_active'))
                                    ->boolean(),
                                IconEntry::make('is_featured')
                                    ->label(__('campaign_product_targets.columns.is_featured'))
                                    ->boolean(),
                            ]),
                    ]),
                InfolistSection::make(__('campaign_product_targets.sections.metadata'))
                    ->schema([
                        InfolistKeyValueEntry::make('conditions')
                            ->label(__('campaign_product_targets.fields.conditions'))
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label(__('campaign_product_targets.fields.notes'))
                            ->columnSpanFull()
                            ->markdown(),
                        TextEntry::make('created_at')
                            ->label(__('campaign_product_targets.columns.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('campaign_product_targets.columns.updated_at'))
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
            'index' => Pages\ListCampaignProductTargets::route('/'),
            'create' => Pages\CreateCampaignProductTarget::route('/create'),
            'view' => Pages\ViewCampaignProductTarget::route('/{record}'),
            'edit' => Pages\EditCampaignProductTarget::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'campaign.name',
            'product.name',
            'category.name',
            'brand.name',
            'collection.name',
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Ensure administrators can manage both active and inactive targets.
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}
