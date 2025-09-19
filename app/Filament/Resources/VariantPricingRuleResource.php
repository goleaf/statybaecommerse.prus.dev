<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Enums\NavigationGroup;
use App\Filament\Resources\VariantPricingRuleResource\Pages;
use App\Models\CustomerGroup;
use App\Models\ProductVariant;
use App\Models\VariantPricingRule;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
 * VariantPricingRuleResource
 *
 * Filament v4 resource for VariantPricingRule management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantPricingRuleResource extends Resource
{
    protected static ?string $model = VariantPricingRule::class;    /** @var UnitEnum|string|null */
    protected static string | UnitEnum | null $navigationGroup = "Products";
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'name';
    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('variant_pricing_rules.title');
    }
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
    public static function getNavigationGroup(): ?string
        return "Products";
     * Handle getPluralModelLabel functionality with proper error handling.
    public static function getPluralModelLabel(): string
        return __('variant_pricing_rules.plural');
     * Handle getModelLabel functionality with proper error handling.
    public static function getModelLabel(): string
        return __('variant_pricing_rules.single');
     * Configure the Filament form schema with fields and validation.
     * @param Form $schema
     * @return Form
    public static function form(Schema $schema): Schema
        return $schema->components([
            Section::make(__('variant_pricing_rules.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('variant_pricing_rules.name'))
                                ->required()
                                ->maxLength(255),
                            Select::make('type')
                                ->label(__('variant_pricing_rules.type'))
                                ->options([
                                    'percentage' => __('variant_pricing_rules.types.percentage'),
                                    'fixed' => __('variant_pricing_rules.types.fixed'),
                                    'tier' => __('variant_pricing_rules.types.tier'),
                                    'bulk' => __('variant_pricing_rules.types.bulk'),
                                ])
                                ->default('percentage')
                                ->live(),
                        ]),
                    Textarea::make('description')
                        ->label(__('variant_pricing_rules.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('variant_pricing_rules.targeting'))
                            Select::make('product_variant_id')
                                ->label(__('variant_pricing_rules.product_variant'))
                                ->relationship('productVariant', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('customer_group_id')
                                ->label(__('variant_pricing_rules.customer_group'))
                                ->relationship('customerGroup', 'name')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->maxLength(500),
                                ]),
            Section::make(__('variant_pricing_rules.pricing'))
                            TextInput::make('value')
                                ->label(__('variant_pricing_rules.value'))
                                ->numeric()
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText(__('variant_pricing_rules.value_help')),
                            TextInput::make('min_quantity')
                                ->label(__('variant_pricing_rules.min_quantity'))
                                ->minValue(1)
                                ->default(1),
                            TextInput::make('max_quantity')
                                ->label(__('variant_pricing_rules.max_quantity'))
                                ->minValue(1),
                            TextInput::make('priority')
                                ->label(__('variant_pricing_rules.priority'))
                                ->default(0)
                                ->helperText(__('variant_pricing_rules.priority_help')),
            Section::make(__('variant_pricing_rules.conditions'))
                            DateTimePicker::make('valid_from')
                                ->label(__('variant_pricing_rules.valid_from'))
                                ->default(now())
                                ->displayFormat('d/m/Y H:i'),
                            DateTimePicker::make('valid_until')
                                ->label(__('variant_pricing_rules.valid_until'))
                            Toggle::make('is_active')
                                ->label(__('variant_pricing_rules.is_active'))
                                ->default(true),
                            Toggle::make('is_cumulative')
                                ->label(__('variant_pricing_rules.is_cumulative'))
                                ->default(false),
        ]);
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
    public static function table(Table $table): Table
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('variant_pricing_rules.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label(__('variant_pricing_rules.type'))
                    ->formatStateUsing(fn(string $state): string => __("variant_pricing_rules.types.{$state}"))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'percentage' => 'blue',
                        'fixed' => 'green',
                        'tier' => 'purple',
                        'bulk' => 'orange',
                        default => 'gray',
                    }),
                TextColumn::make('productVariant.name')
                    ->label(__('variant_pricing_rules.product_variant'))
                    ->limit(50),
                TextColumn::make('customerGroup.name')
                    ->label(__('variant_pricing_rules.customer_group'))
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('value')
                    ->label(__('variant_pricing_rules.value'))
                    ->numeric()
                    ->formatStateUsing(function ($state, $record): string {
                        if ($record->type === 'percentage') {
                            return $state . '%';
                        }
                        return '€' . number_format($state, 2);
                TextColumn::make('min_quantity')
                    ->label(__('variant_pricing_rules.min_quantity'))
                    ->alignCenter()
                TextColumn::make('max_quantity')
                    ->label(__('variant_pricing_rules.max_quantity'))
                TextColumn::make('priority')
                    ->label(__('variant_pricing_rules.priority'))
                IconColumn::make('is_active')
                    ->label(__('variant_pricing_rules.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_cumulative')
                    ->label(__('variant_pricing_rules.is_cumulative'))
                TextColumn::make('valid_from')
                    ->label(__('variant_pricing_rules.valid_from'))
                    ->dateTime()
                TextColumn::make('valid_until')
                    ->label(__('variant_pricing_rules.valid_until'))
                TextColumn::make('created_at')
                    ->label(__('variant_pricing_rules.created_at'))
                TextColumn::make('updated_at')
                    ->label(__('variant_pricing_rules.updated_at'))
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'percentage' => __('variant_pricing_rules.types.percentage'),
                        'fixed' => __('variant_pricing_rules.types.fixed'),
                        'tier' => __('variant_pricing_rules.types.tier'),
                        'bulk' => __('variant_pricing_rules.types.bulk'),
                    ]),
                SelectFilter::make('product_variant_id')
                    ->relationship('productVariant', 'name')
                    ->preload(),
                SelectFilter::make('customer_group_id')
                    ->relationship('customerGroup', 'name')
                TernaryFilter::make('is_active')
                    ->trueLabel(__('variant_pricing_rules.active_only'))
                    ->falseLabel(__('variant_pricing_rules.inactive_only'))
                    ->native(false),
                TernaryFilter::make('is_cumulative')
                    ->trueLabel(__('variant_pricing_rules.cumulative_only'))
                    ->falseLabel(__('variant_pricing_rules.non_cumulative_only'))
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(fn(VariantPricingRule $record): string => $record->is_active ? __('variant_pricing_rules.deactivate') : __('variant_pricing_rules.activate'))
                    ->icon(fn(VariantPricingRule $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(VariantPricingRule $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (VariantPricingRule $record): void {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title($record->is_active ? __('variant_pricing_rules.activated_successfully') : __('variant_pricing_rules.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('variant_pricing_rules.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title(__('variant_pricing_rules.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('variant_pricing_rules.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                            $records->each->update(['is_active' => false]);
                                ->title(__('variant_pricing_rules.bulk_deactivated_success'))
            ->defaultSort('priority', 'desc');
     * Get the relations for this resource.
     * @return array
    public static function getRelations(): array
        return [
            //
        ];
     * Get the pages for this resource.
    public static function getPages(): array
            'index' => Pages\ListVariantPricingRules::route('/'),
            'create' => Pages\CreateVariantPricingRule::route('/create'),
            'view' => Pages\ViewVariantPricingRule::route('/{record}'),
            'edit' => Pages\EditVariantPricingRule::route('/{record}/edit'),
}
