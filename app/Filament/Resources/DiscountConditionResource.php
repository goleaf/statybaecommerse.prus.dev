<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DiscountConditionResource\Pages;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\Product;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
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

    /**
     * @var UnitEnum|string|null
     */
    protected static string|UnitEnum|null $navigationGroup = 'Products';

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
        return 'Marketing'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('discount_conditions.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('discount_conditions.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('discount_conditions.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('discount_conditions.name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('discount_code_id')
                                ->label(__('discount_conditions.discount_code'))
                                ->relationship('discountCode', 'code')
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
            Section::make(__('discount_conditions.condition_settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('discount_conditions.type'))
                                ->options([
                                    'product' => __('discount_conditions.types.product'),
                                    'category' => __('discount_conditions.types.category'),
                                    'quantity' => __('discount_conditions.types.quantity'),
                                    'amount' => __('discount_conditions.types.amount'),
                                    'customer' => __('discount_conditions.types.customer'),
                                    'date' => __('discount_conditions.types.date'),
                                ])
                                ->required()
                                ->default('product')
                                ->live(),
                            Select::make('operator')
                                ->label(__('discount_conditions.operator'))
                                ->options([
                                    'equals' => __('discount_conditions.operators.equals'),
                                    'not_equals' => __('discount_conditions.operators.not_equals'),
                                    'greater_than' => __('discount_conditions.operators.greater_than'),
                                    'less_than' => __('discount_conditions.operators.less_than'),
                                    'greater_than_or_equal' => __('discount_conditions.operators.greater_than_or_equal'),
                                    'less_than_or_equal' => __('discount_conditions.operators.less_than_or_equal'),
                                    'contains' => __('discount_conditions.operators.contains'),
                                    'not_contains' => __('discount_conditions.operators.not_contains'),
                                    'in' => __('discount_conditions.operators.in'),
                                    'not_in' => __('discount_conditions.operators.not_in'),
                                ])
                                ->required()
                                ->default('equals'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('value')
                                ->label(__('discount_conditions.value'))
                                ->required()
                                ->maxLength(255)
                                ->helperText(__('discount_conditions.value_help')),
                            TextInput::make('priority')
                                ->label(__('discount_conditions.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('discount_conditions.priority_help')),
                        ]),
                ]),
            Section::make(__('discount_conditions.targeting'))
                ->schema([
                    Grid::make(2)
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
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_exclusive')
                                ->label(__('discount_conditions.is_exclusive'))
                                ->default(false),
                            Toggle::make('is_cumulative')
                                ->label(__('discount_conditions.is_cumulative'))
                                ->default(false),
                        ]),
                ]),
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
                TextColumn::make('discountCode.code')
                    ->label(__('discount_conditions.discount_code'))
                    ->sortable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->formatStateUsing(fn(string $state): string => __("discount_conditions.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'product' => 'green',
                        'category' => 'blue',
                        'quantity' => 'purple',
                        'amount' => 'orange',
                        'customer' => 'pink',
                        'date' => 'indigo',
                        default => 'gray',
                    }),
                TextColumn::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->formatStateUsing(fn(string $state): string => __("discount_conditions.operators.{$state}"))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('priority')
                    ->label(__('discount_conditions.priority'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('products_count')
                    ->label(__('discount_conditions.products_count'))
                    ->counts('products')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categories_count')
                    ->label(__('discount_conditions.categories_count'))
                    ->counts('categories')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label(__('discount_conditions.is_required'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_exclusive')
                    ->label(__('discount_conditions.is_exclusive'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_cumulative')
                    ->label(__('discount_conditions.is_cumulative'))
                    ->boolean()
                    ->sortable()
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
                SelectFilter::make('discount_code_id')
                    ->label(__('discount_conditions.discount_code'))
                    ->relationship('discountCode', 'code')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('discount_conditions.type'))
                    ->options([
                        'product' => __('discount_conditions.types.product'),
                        'category' => __('discount_conditions.types.category'),
                        'quantity' => __('discount_conditions.types.quantity'),
                        'amount' => __('discount_conditions.types.amount'),
                        'customer' => __('discount_conditions.types.customer'),
                        'date' => __('discount_conditions.types.date'),
                    ]),
                SelectFilter::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->options([
                        'equals' => __('discount_conditions.operators.equals'),
                        'not_equals' => __('discount_conditions.operators.not_equals'),
                        'greater_than' => __('discount_conditions.operators.greater_than'),
                        'less_than' => __('discount_conditions.operators.less_than'),
                        'greater_than_or_equal' => __('discount_conditions.operators.greater_than_or_equal'),
                        'less_than_or_equal' => __('discount_conditions.operators.less_than_or_equal'),
                        'contains' => __('discount_conditions.operators.contains'),
                        'not_contains' => __('discount_conditions.operators.not_contains'),
                        'in' => __('discount_conditions.operators.in'),
                        'not_in' => __('discount_conditions.operators.not_in'),
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->trueLabel(__('discount_conditions.active_only'))
                    ->falseLabel(__('discount_conditions.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_required')
                    ->label(__('discount_conditions.is_required'))
                    ->boolean()
                    ->trueLabel(__('discount_conditions.required_only'))
                    ->falseLabel(__('discount_conditions.optional_only'))
                    ->native(false),
                TernaryFilter::make('is_exclusive')
                    ->label(__('discount_conditions.is_exclusive'))
                    ->boolean()
                    ->trueLabel(__('discount_conditions.exclusive_only'))
                    ->falseLabel(__('discount_conditions.non_exclusive_only'))
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
     * @return array
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
