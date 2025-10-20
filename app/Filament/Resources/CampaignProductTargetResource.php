<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignProductTargetResource\Pages;
use App\Models\CampaignProductTarget;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

final class CampaignProductTargetResource extends Resource
{
    protected static ?string $model = CampaignProductTarget::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return 'heroicon-o-bullseye';
    }

    protected static ?string $recordTitleAttribute = 'target_name';

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public static function getPluralModelLabel(): string
    {
        return __('Campaign Product Targets');
    }

    public static function getModelLabel(): string
    {
        return __('Campaign Product Target');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('Target details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('campaign_id')
                                ->label(__('Campaign'))
                                ->relationship('campaign', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('target_type')
                                ->label(__('Target type'))
                                ->options([
                                    'product' => __('Product'),
                                    'category' => __('Category'),
                                    'brand' => __('Brand'),
                                    'collection' => __('Collection'),
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(static function (Forms\Set $set): void {
                                    $set('product_id', null);
                                    $set('category_id', null);
                                    $set('brand_id', null);
                                    $set('collection_id', null);
                                }),
                        ]),
                    Select::make('product_id')
                        ->label(__('Product'))
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'product')
                        ->required(fn (Forms\Get $get): bool => $get('target_type') === 'product'),
                    Select::make('category_id')
                        ->label(__('Category'))
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'category')
                        ->required(fn (Forms\Get $get): bool => $get('target_type') === 'category'),
                    Select::make('brand_id')
                        ->label(__('Brand'))
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'brand')
                        ->required(fn (Forms\Get $get): bool => $get('target_type') === 'brand'),
                    Select::make('collection_id')
                        ->label(__('Collection'))
                        ->relationship('collection', 'name')
                        ->searchable()
                        ->preload()
                        ->visible(fn (Forms\Get $get): bool => $get('target_type') === 'collection')
                        ->required(fn (Forms\Get $get): bool => $get('target_type') === 'collection'),
                ])
                ->columns(2),
            Section::make(__('Target settings'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('priority')
                                ->label(__('Priority'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(50)
                                ->required(),
                            TextInput::make('weight')
                                ->label(__('Weight'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('sort_order')
                                ->label(__('Sort order'))
                                ->numeric()
                                ->default(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('Active'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('Featured'))
                                ->default(false),
                        ]),
                    KeyValue::make('conditions')
                        ->label(__('Conditions'))
                        ->keyLabel(__('Field'))
                        ->valueLabel(__('Value'))
                        ->addButtonLabel(__('Add condition'))
                        ->columnSpanFull(),
                    Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('campaign.name')
                    ->label(__('Campaign'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('target_type')
                    ->label(__('Target type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Str::of($state)->headline()),
                TextColumn::make('target_name')
                    ->label(__('Target'))
                    ->searchable(),
                TextColumn::make('priority')
                    ->label(__('Priority'))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('campaign_id')
                    ->label(__('Campaign'))
                    ->relationship('campaign', 'name'),
                SelectFilter::make('target_type')
                    ->label(__('Target type'))
                    ->options([
                        'product' => __('Product'),
                        'category' => __('Category'),
                        'brand' => __('Brand'),
                        'collection' => __('Collection'),
                    ]),
                SelectFilter::make('is_active')
                    ->label(__('Active'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ])
                    ->query(fn (Builder $query, string $value): Builder => $query->where('is_active', (bool) (int) $value)),
                Filter::make('high_priority')
                    ->label(__('High priority'))
                    ->query(fn (Builder $query): Builder => $query->where('priority', '>=', 80)),
                Filter::make('recent')
                    ->label(__('Recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('Activate selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('Selected targets have been activated.'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('Deactivate selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('Selected targets have been deactivated.'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('Feature selected'))
                        ->icon('heroicon-o-star')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => true]);

                            Notification::make()
                                ->title(__('Selected targets have been featured.'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unfeature')
                        ->label(__('Unfeature selected'))
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_featured' => false]);

                            Notification::make()
                                ->title(__('Selected targets have been unfeatured.'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['campaign', 'product', 'category', 'brand', 'collection']);
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
}
