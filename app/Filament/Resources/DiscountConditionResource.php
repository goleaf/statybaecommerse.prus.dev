<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountConditionResource\Pages;
use App\Models\DiscountCondition;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class DiscountConditionResource extends Resource
{
    protected static ?string $model = DiscountCondition::class;

    /** @var string|BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Discounts';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('discount_conditions.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('discount_conditions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('discount_conditions.single');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('discount_condition_form')
                ->tabs([
                    Tab::make(__('discount_conditions.basic_information'))
                        ->schema([
                            Select::make('discount_id')
                                ->label(__('discount_conditions.discount'))
                                ->relationship('discount', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Repeater::make('translations')
                                ->label(__('discount_conditions.translations'))
                                ->relationship('translations')
                                ->schema([
                                    Select::make('locale')
                                        ->label(__('discount_conditions.locale'))
                                        ->options(self::supportedLocaleOptions())
                                        ->required(),
                                    TextInput::make('name')
                                        ->label(__('discount_conditions.name'))
                                        ->maxLength(255)
                                        ->required(),
                                    Textarea::make('description')
                                        ->label(__('discount_conditions.description'))
                                        ->rows(2)
                                        ->maxLength(1000)
                                        ->columnSpanFull(),
                                    KeyValue::make('metadata')
                                        ->label(__('discount_conditions.metadata'))
                                        ->keyLabel(__('discount_conditions.metadata_key'))
                                        ->valueLabel(__('discount_conditions.metadata_value'))
                                        ->columnSpanFull()
                                        ->addButtonLabel(__('discount_conditions.add_metadata_item'))
                                        ->nullable(),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->defaultItems(0)
                                ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                        ]),
                    Tab::make(__('discount_conditions.condition_settings'))
                        ->schema([
                            Select::make('type')
                                ->label(__('discount_conditions.type'))
                                ->options(DiscountCondition::getTypes())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live(),
                            Select::make('operator')
                                ->label(__('discount_conditions.operator'))
                                ->options(fn (Get $get): array => DiscountCondition::getOperatorsForType($get('type') ?? 'cart_total'))
                                ->required()
                                ->searchable()
                                ->preload(),
                            Textarea::make('value')
                                ->label(__('discount_conditions.value'))
                                ->rows(3)
                                ->required()
                                ->helperText(__('discount_conditions.value_help'))
                                ->afterStateHydrated(function (Textarea $component, mixed $state): void {
                                    if (blank($state)) {
                                        $component->state(null);

                                        return;
                                    }

                                    $component->state(self::encodeValueForTextarea($state));
                                })
                                ->dehydrateStateUsing(fn (mixed $state): mixed => self::normalizeValue($state)),
                            TextInput::make('priority')
                                ->label(__('discount_conditions.priority'))
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText(__('discount_conditions.priority_help')),
                            TextInput::make('position')
                                ->label(__('discount_conditions.position'))
                                ->numeric()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label(__('discount_conditions.is_active'))
                                ->default(true),
                        ])->columns(2),
                    Tab::make(__('discount_conditions.targeting'))
                        ->schema([
                            Select::make('products')
                                ->label(__('discount_conditions.products'))
                                ->relationship('products', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                            Select::make('categories')
                                ->label(__('discount_conditions.categories'))
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->columnSpanFull(),
                        ]),
                    Tab::make(__('discount_conditions.settings'))
                        ->schema([
                            KeyValue::make('metadata')
                                ->label(__('discount_conditions.metadata'))
                                ->keyLabel(__('discount_conditions.metadata_key'))
                                ->valueLabel(__('discount_conditions.metadata_value'))
                                ->addButtonLabel(__('discount_conditions.add_metadata_item'))
                                ->nullable()
                                ->columnSpanFull()
                                ->helperText(__('discount_conditions.metadata_help')),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('translated_name')
                    ->label(__('discount_conditions.name'))
                    ->toggleable()
                    ->limit(50)
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('discount.name')
                    ->label(__('discount_conditions.discount'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __('discount_conditions.types.' . Str::slug($state, '_')))
                    ->color(fn (string $state): string => self::typeColor($state))
                    ->sortable(),
                TextColumn::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->formatStateUsing(fn (string $state): string => __('discount_conditions.operators.' . Str::slug($state, '_')))
                    ->badge()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->formatStateUsing(fn (mixed $state): string => self::formatValueForDisplay($state))
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(__('discount_conditions.priority'))
                    ->sortable(),
                TextColumn::make('position')
                    ->label(__('discount_conditions.position'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('discount_conditions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('discount_conditions.type'))
                    ->options(DiscountCondition::getTypes()),
                SelectFilter::make('discount_id')
                    ->label(__('discount_conditions.discount'))
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('discount_conditions.status_filter')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                TableAction::make('toggle_active')
                    ->label(fn (DiscountCondition $record): string => $record->is_active
                        ? __('discount_conditions.deactivate')
                        : __('discount_conditions.activate'))
                    ->icon(fn (DiscountCondition $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (DiscountCondition $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (DiscountCondition $record): void {
                        $record->is_active = ! $record->is_active;
                        $record->save();

                        Notification::make()
                            ->title($record->is_active ? __('discount_conditions.activated_successfully') : __('discount_conditions.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('discount_conditions.activate_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (DiscountCondition $record) => $record->update(['is_active' => true]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label(__('discount_conditions.deactivate_selected'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (DiscountCondition $record) => $record->update(['is_active' => false]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('set_priority')
                        ->label(__('discount_conditions.set_priority'))
                        ->icon('heroicon-o-arrow-up-tray')
                        ->form([
                            TextInput::make('priority')
                                ->label(__('discount_conditions.priority'))
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (DiscountCondition $record) => $record->update(['priority' => (int) $data['priority']]));
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
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
            'index'  => Pages\ListDiscountConditions::route('/'),
            'create' => Pages\CreateDiscountCondition::route('/create'),
            'view'   => Pages\ViewDiscountCondition::route('/{record}'),
            'edit'   => Pages\EditDiscountCondition::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['discount', 'translations']);
    }

    private static function supportedLocaleOptions(): array
    {
        $locales = config('shared.supported_locales') ?? config('app.supported_locales', ['lt', 'en']);

        if (is_string($locales)) {
            $locales = array_map('trim', explode(',', $locales));
        }

        return collect($locales)
            ->filter()
            ->unique()
            ->mapWithKeys(static fn (string $locale): array => [$locale => Str::upper($locale)])
            ->toArray();
    }

    private static function encodeValueForTextarea(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        $decoded = json_decode((string) $value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return (string) $value;
    }

    private static function formatValueForDisplay(mixed $value): string
    {
        $value = self::normalizeValue($value);

        if (is_array($value)) {
            return implode(', ', Arr::flatten($value));
        }

        if (is_bool($value)) {
            return $value ? __('discount_conditions.boolean_yes') : __('discount_conditions.boolean_no');
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return (string) $value;
    }

    private static function typeColor(string $type): string
    {
        return match ($type) {
            'cart_total' => 'primary',
            'item_qty'   => 'success',
            'product', 'attribute_value' => 'info',
            'category', 'collection' => 'warning',
            'brand' => 'purple',
            'channel', 'currency' => 'indigo',
            'customer_group', 'partner_tier' => 'cyan',
            'user'          => 'teal',
            'first_order'   => 'emerald',
            'day_time'      => 'amber',
            'custom_script' => 'pink',
            default         => 'gray',
        };
    }
}
