<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Components\Combobox;
use App\Filament\Resources\DiscountConditionResource\Pages;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionChartWidget;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionStatsWidget;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionTableWidget;
use App\Models\DiscountCondition;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class DiscountConditionResource extends Resource
{
    protected static ?string $model = DiscountCondition::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-adjustments-horizontal';

    public static function getNavigationGroup(): ?string
    {
        return 'Discounts';
    }

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
        // Filament 4 expects returning the Form builder instance.
        return $form
            ->schema([
                Tabs::make('discount_condition')
                    ->tabs([
                        Tab::make(__('discount_conditions.basic_information'))
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('discount_id')
                                            ->label(__('discount_conditions.discount'))
                                            ->relationship('discount', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('position')
                                            ->label(__('discount_conditions.position'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0),
                                        TextInput::make('priority')
                                            ->label(__('discount_conditions.priority'))
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->helperText(__('discount_conditions.priority_help')),
                                        Toggle::make('is_active')
                                            ->label(__('discount_conditions.is_active'))
                                            ->default(true),
                                    ]),
                            ]),
                        Tab::make(__('discount_conditions.condition_settings'))
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('type')
                                            ->label(__('discount_conditions.type'))
                                            ->options(static fn (): array => DiscountCondition::getTypes())
                                            ->required()
                                            ->live(),
                                        Select::make('operator')
                                            ->label(__('discount_conditions.operator'))
                                            ->options(static fn (Get $get): array => DiscountCondition::getOperatorsForType($get('type') ?? ''))
                                            ->required()
                                            ->live(),
                                        Textarea::make('value')
                                            ->label(__('discount_conditions.value'))
                                            ->rows(4)
                                            ->helperText(__('discount_conditions.value_help'))
                                            ->required()
                                            ->columnSpanFull()
                                            ->afterStateHydrated(static function (Textarea $component, mixed $state): void {
                                                $component->state(self::encodeValueForTextarea($state));
                                            })
                                            ->dehydrateStateUsing(static fn (?string $state): mixed => self::decodeValueFromTextarea($state)),
                                        Textarea::make('metadata')
                                            ->label(__('discount_conditions.metadata'))
                                            ->rows(4)
                                            ->helperText(__('discount_conditions.metadata_help'))
                                            ->columnSpanFull()
                                            ->afterStateHydrated(static function (Textarea $component, mixed $state): void {
                                                $component->state(self::encodeValueForTextarea($state));
                                            })
                                            ->dehydrateStateUsing(static fn (?string $state): mixed => self::decodeValueFromTextarea($state)),
                                    ]),
                            ]),
                        Tab::make(__('discount_conditions.targeting'))
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Combobox::make('products')
                                            ->label(__('discount_conditions.products'))
                                            ->relationship('products', 'name')
                                            ->preload()
                                            ->height('340px')
                                            ->translatedLabels(
                                                'discount_conditions.products_options_label',
                                                'discount_conditions.products_selected_label',
                                            )
                                            ->multiple()
                                            ->columnSpanFull(),
                                        Combobox::make('categories')
                                            ->label(__('discount_conditions.categories'))
                                            ->relationship('categories', 'name')
                                            ->preload()
                                            ->height('340px')
                                            ->translatedLabels(
                                                'discount_conditions.categories_options_label',
                                                'discount_conditions.categories_selected_label',
                                            )
                                            ->multiple()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('discount.name')
                    ->label(__('discount_conditions.discount'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('discount_conditions.type'))
                    ->badge()
                    ->formatStateUsing(static fn (?string $state): string => $state ? (DiscountCondition::getTypes()[$state] ?? Str::of($state)->headline()->toString()) : '-')
                    ->sortable(),
                TextColumn::make('operator')
                    ->label(__('discount_conditions.operator'))
                    ->formatStateUsing(static fn (?string $state): string => $state ? (DiscountCondition::getOperators()[$state] ?? Str::of($state)->headline()->toString()) : '-')
                    ->badge()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('discount_conditions.value'))
                    ->formatStateUsing(static fn (mixed $state): string => self::formatValueForTable($state))
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('priority')
                    ->label(__('discount_conditions.priority'))
                    ->sortable(),
                TextColumn::make('position')
                    ->label(__('discount_conditions.position'))
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('discount_conditions.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('discount_conditions.updated_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('discount_conditions.type'))
                    ->options(static fn (): array => DiscountCondition::getTypes())
                    ->searchable(),
                SelectFilter::make('discount_id')
                    ->label(__('discount_conditions.discount'))
                    ->relationship('discount', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_active')
                    ->label(__('discount_conditions.is_active'))
                    ->options([
                        '1' => __('discount_conditions.active_only'),
                        '0' => __('discount_conditions.inactive_only'),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        if (! array_key_exists('value', $data) || $data['value'] === null || $data['value'] === '') {
                            return $query;
                        }

                        return $query->where('is_active', (bool) (int) $data['value']);
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_active')
                    ->label(static fn (DiscountCondition $record): string => $record->is_active ? __('discount_conditions.deactivate') : __('discount_conditions.activate'))
                    ->icon('heroicon-o-power')
                    ->color(static fn (DiscountCondition $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(static function (DiscountCondition $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('discount_conditions.activated_successfully') : __('discount_conditions.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label(__('discount_conditions.activate_selected'))
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(static function (Collection $records): void {
                            $records->each(static function (DiscountCondition $record): void {
                                $record->update(['is_active' => true]);
                            });

                            Notification::make()
                                ->title(__('discount_conditions.bulk_activated_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('deactivate')
                        ->label(__('discount_conditions.deactivate_selected'))
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(static function (Collection $records): void {
                            $records->each(static function (DiscountCondition $record): void {
                                $record->update(['is_active' => false]);
                            });

                            Notification::make()
                                ->title(__('discount_conditions.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'asc');
    }

    public static function infolist(Schema $schema): Schema|array
    {
        return $schema
            ->components([
                InfolistSection::make(__('discount_conditions.basic_information'))
                    ->schema([
                        InfolistGrid::make()
                            ->schema([
                                TextEntry::make('discount.name')
                                    ->label(__('discount_conditions.discount')),
                                TextEntry::make('type')
                                    ->label(__('discount_conditions.type'))
                                    ->formatStateUsing(static fn (?string $state): string => $state ? (DiscountCondition::getTypes()[$state] ?? Str::of($state)->headline()->toString()) : '-')
                                    ->badge(),
                                TextEntry::make('operator')
                                    ->label(__('discount_conditions.operator'))
                                    ->formatStateUsing(static fn (?string $state): string => $state ? (DiscountCondition::getOperators()[$state] ?? Str::of($state)->headline()->toString()) : '-')
                                    ->badge(),
                                IconEntry::make('is_active')
                                    ->label(__('discount_conditions.is_active'))
                                    ->boolean(),
                                TextEntry::make('priority')
                                    ->label(__('discount_conditions.priority')),
                                TextEntry::make('position')
                                    ->label(__('discount_conditions.position')),
                            ])
                            ->columns(3),
                    ]),
                InfolistSection::make(__('discount_conditions.condition_settings'))
                    ->schema([
                        TextEntry::make('value')
                            ->label(__('discount_conditions.value'))
                            ->formatStateUsing(static fn (mixed $state): string => self::formatValueForTable($state))
                            ->columnSpanFull(),
                        TextEntry::make('metadata')
                            ->label(__('discount_conditions.metadata'))
                            ->formatStateUsing(static fn (mixed $state): string => self::formatValueForTable($state))
                            ->columnSpanFull(),
                    ]),
                InfolistSection::make(__('discount_conditions.targeting'))
                    ->schema([
                        TextEntry::make('products.name')
                            ->label(__('discount_conditions.products'))
                            ->badge()
                            ->separator(', '),
                        TextEntry::make('categories.name')
                            ->label(__('discount_conditions.categories'))
                            ->badge()
                            ->separator(', '),
                    ])
                    ->columns(2),
                InfolistSection::make(__('discount_conditions.created_at'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('discount_conditions.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('discount_conditions.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            DiscountConditionStatsWidget::class,
            DiscountConditionChartWidget::class,
            DiscountConditionTableWidget::class,
        ];
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

    private static function encodeValueForTextarea(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private static function decodeValueFromTextarea(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        if (Str::contains($trimmed, ',')) {
            return array_map('trim', explode(',', $trimmed));
        }

        if (is_numeric($trimmed)) {
            return $trimmed + 0;
        }

        return $trimmed;
    }

    private static function formatValueForTable(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_bool($value)) {
            return $value ? __('common.yes') : __('common.no');
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $flattened = Arr::flatten($value);

            if (count($flattened) === 1) {
                $single = reset($flattened);

                return is_scalar($single) ? (string) $single : json_encode($single, JSON_UNESCAPED_UNICODE);
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return (string) $value;
    }
}
