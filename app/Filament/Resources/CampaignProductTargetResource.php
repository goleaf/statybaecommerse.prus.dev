<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\CampaignProductTarget;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use UnitEnum;

final class CampaignProductTargetResource extends Resource
{
    protected static ?string $model = CampaignProductTarget::class;

    protected static UnitEnum|string|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 8;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-bullseye';
    }

    public static function getNavigationLabel(): string
    {
        return __('campaign_product_targets.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('campaign_product_targets.models.plural');
    }

    public static function getModelLabel(): string
    {
        return __('campaign_product_targets.models.singular');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('campaign_id')
                ->label(__('campaign_product_targets.fields.campaign'))
                ->relationship('campaign', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('target_type')
                ->label(__('campaign_product_targets.fields.target_type'))
                ->options([
                    'product' => __('campaign_product_targets.target_types.product'),
                    'category' => __('campaign_product_targets.target_types.category'),
                    'brand' => __('campaign_product_targets.target_types.brand'),
                    'collection' => __('campaign_product_targets.target_types.collection'),
                ])
                ->required()
                ->live(),
            Select::make('product_id')
                ->label(__('campaign_product_targets.fields.product'))
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('target_type') === 'product')
                ->required(fn (Get $get): bool => $get('target_type') === 'product')
                ->nullable(),
            Select::make('category_id')
                ->label(__('campaign_product_targets.fields.category'))
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('target_type') === 'category')
                ->required(fn (Get $get): bool => $get('target_type') === 'category')
                ->nullable(),
            Select::make('brand_id')
                ->label(__('campaign_product_targets.fields.brand'))
                ->relationship('brand', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('target_type') === 'brand')
                ->required(fn (Get $get): bool => $get('target_type') === 'brand')
                ->nullable(),
            Select::make('collection_id')
                ->label(__('campaign_product_targets.fields.collection'))
                ->relationship('collection', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get): bool => $get('target_type') === 'collection')
                ->required(fn (Get $get): bool => $get('target_type') === 'collection')
                ->nullable(),
            TextInput::make('priority')
                ->label(__('campaign_product_targets.fields.priority'))
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(50)
                ->required(),
            TextInput::make('weight')
                ->label(__('campaign_product_targets.fields.weight'))
                ->numeric()
                ->minValue(0)
                ->default(0),
            TextInput::make('sort_order')
                ->label(__('campaign_product_targets.fields.sort_order'))
                ->numeric()
                ->minValue(0)
                ->default(0),
            Toggle::make('is_active')
                ->label(__('campaign_product_targets.fields.is_active'))
                ->default(true),
            Toggle::make('is_featured')
                ->label(__('campaign_product_targets.fields.is_featured'))
                ->default(false),
            Textarea::make('conditions')
                ->label(__('campaign_product_targets.fields.conditions'))
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('notes')
                ->label(__('campaign_product_targets.fields.notes'))
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('campaign_product_targets.table.id'))
                    ->sortable(),
                TextColumn::make('campaign.name')
                    ->label(__('campaign_product_targets.table.campaign'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label(__('campaign_product_targets.table.target_type'))
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'product' => __('campaign_product_targets.target_types.product'),
                            'category' => __('campaign_product_targets.target_types.category'),
                            'brand' => __('campaign_product_targets.target_types.brand'),
                            'collection' => __('campaign_product_targets.target_types.collection'),
                            default => __('campaign_product_targets.target_types.unknown'),
                        }
                    )
                    ->sortable(),
                TextColumn::make('target_name')
                    ->label(__('campaign_product_targets.table.target_name'))
                    ->searchable([
                        'product.name',
                        'category.name',
                        'brand.name',
                        'collection.name',
                    ])
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(__('campaign_product_targets.table.priority'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('campaign_product_targets.table.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('campaign_product_targets.table.is_featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('campaign_product_targets.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('campaign_product_targets.filters.campaign'))
                    ->relationship('campaign', 'name')
                    ->searchable(),
                SelectFilter::make('target_type')
                    ->label(__('campaign_product_targets.filters.target_type'))
                    ->options([
                        'product' => __('campaign_product_targets.target_types.product'),
                        'category' => __('campaign_product_targets.target_types.category'),
                        'brand' => __('campaign_product_targets.target_types.brand'),
                        'collection' => __('campaign_product_targets.target_types.collection'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('campaign_product_targets.filters.is_active')),
                Filter::make('high_priority')
                    ->label(__('campaign_product_targets.filters.high_priority'))
                    ->query(fn (Builder $query): Builder => $query->where('priority', '>=', 80)),
                Filter::make('recent')
                    ->label(__('campaign_product_targets.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', Carbon::now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_active')
                    ->label(__('campaign_product_targets.actions.toggle_active'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('secondary')
                    ->action(function (CampaignProductTarget $record): void {
                        $record->update(['is_active' => ! $record->is_active]);
                        Notification::make()
                            ->title($record->is_active
                                ? __('campaign_product_targets.notifications.activated')
                                : __('campaign_product_targets.notifications.deactivated'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('campaign_product_targets.bulk_actions.activate'))
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.bulk_activated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('campaign_product_targets.bulk_actions.deactivate'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.bulk_deactivated'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('feature')
                        ->label(__('campaign_product_targets.bulk_actions.feature'))
                        ->icon('heroicon-o-star')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.bulk_featured'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('unfeature')
                        ->label(__('campaign_product_targets.bulk_actions.unfeature'))
                        ->icon('heroicon-o-x-circle')
                        ->color('secondary')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('campaign_product_targets.notifications.bulk_unfeatured'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['campaign', 'product', 'category', 'brand', 'collection']);
    }
}
