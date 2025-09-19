<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DiscountConditionResource\Pages;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * DiscountConditionResource
 *
 * Filament v4 resource for DiscountCondition management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class DiscountConditionResource extends Resource
{
    protected static ?string $model = DiscountCondition::class;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('discount_conditions.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('discount_conditions.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('discount_conditions.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('discount_conditions.tabs'))
                    ->tabs([
                        Tab::make(__('discount_conditions.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('discount_conditions.basic_information'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('discount_conditions.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live(),
                                                Select::make('discount_id')
                                                    ->label(__('discount_conditions.discount'))
                                                    ->relationship('discount', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required(),
                                            ]),
                                        Textarea::make('description')
                                            ->label(__('discount_conditions.description'))
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make(__('discount_conditions.condition_settings'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make(__('discount_conditions.condition_settings'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('type')
                                                    ->label(__('discount_conditions.type'))
                                                    ->options(DiscountCondition::getTypes())
                                                    ->default('product')
                                                    ->live()
                                                    ->required(),
                                                Select::make('operator')
                                                    ->label(__('discount_conditions.operator'))
                                                    ->options(DiscountCondition::getOperators())
                                                    ->default('equals_to')
                                                    ->required(),
                                            ]),
                                        TextInput::make('value')
                                            ->label(__('discount_conditions.value'))
                                            ->maxLength(255)
                                            ->helperText(__('discount_conditions.value_help'))
                                            ->columnSpanFull(),
                                        TextInput::make('priority')
                                            ->label(__('discount_conditions.priority'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->helperText(__('discount_conditions.priority_help')),
                                    ]),
                            ]),
                        Tab::make(__('discount_conditions.targeting'))
                            ->icon('heroicon-o-target')
                            ->schema([
                                Section::make(__('discount_conditions.targeting'))
                                    ->schema([
                                        Select::make('products')
                                            ->label(__('discount_conditions.products'))
                                            ->relationship('products', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'product'),
                                        Select::make('categories')
                                            ->label(__('discount_conditions.categories'))
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->searchable()
                                            ->preload()
                                            ->visible(fn(Forms\Get $get): bool => $get('type') === 'category'),
                                    ]),
                            ]),
                        Tab::make(__('discount_conditions.settings'))
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Section::make(__('discount_conditions.settings'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_active')
                                                    ->label(__('discount_conditions.is_active'))
                                                    ->default(true),
                                                Toggle::make('is_required')
                                                    ->label(__('discount_conditions.is_required'))
                                                    ->default(false),
                                            ]),
                                        KeyValue::make('metadata')
                                            ->label(__('discount_conditions.metadata'))
                                            ->helperText(__('discount_conditions.metadata_help'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('discount_conditions.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('discount.name')
                    ->label(__('discount_conditions.discount'))
                    ->badge()
                    ->color('blue')
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->formatStateUsing(fn(string $state): string => __("discount_conditions.types.{$state}"))
                    ->color(fn(string $state): string => match ($state) {
                        'product' => 'green',
                        'category' => 'blue',
                        'cart_total' => 'purple',
                        'item_qty' => 'orange',
                        'customer_group' => 'pink',
                        'day_time' => 'indigo',
                        default => 'gray',
                    }),
                TextColumn::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->formatStateUsing(fn(string $state): string => __("discount_conditions.operators.{$state}"))
                    ->color('gray'),
                TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),
                TextColumn::make('priority')
                    ->label(__('discount_conditions.priority'))
                    ->numeric()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('discount_conditions.products_count'))
                    ->counts('products')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories_count')
                    ->label(__('discount_conditions.categories_count'))
                    ->counts('categories')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('discount_conditions.is_required'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('discount_conditions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('discount_conditions.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('discount_id')
                    ->relationship('discount', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('type')
                    ->options(DiscountCondition::getTypes()),
                SelectFilter::make('operator')
                    ->options(DiscountCondition::getOperators()),
                TernaryFilter::make('is_active')
                    ->trueLabel(__('discount_conditions.active_only'))
                    ->falseLabel(__('discount_conditions.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->trueLabel(__('discount_conditions.required_only'))
                    ->falseLabel(__('discount_conditions.optional_only'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(DiscountCondition $record): string => $record->is_active ? __('discount_conditions.deactivate') : __('discount_conditions.activate'))
                    ->icon(fn(DiscountCondition $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(DiscountCondition $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (DiscountCondition $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('discount_conditions.activated_successfully') : __('discount_conditions.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('discount_conditions.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('discount_conditions.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('discount_conditions.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title(__('discount_conditions.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('priority', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscountConditions::route('/'),
            'create' => Pages\CreateDiscountCondition::route('/create'),
            'view' => Pages\ViewDiscountCondition::route('/{record}'),
            'edit' => Pages\EditDiscountCondition::route('/{record}/edit'),
        ];
    }
}
