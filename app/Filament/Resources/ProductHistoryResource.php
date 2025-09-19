<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductHistoryResource\Widgets\ProductHistoryStatsWidget;
use App\Filament\Resources\ProductHistoryResource\Widgets\RecentProductChangesWidget;
use App\Filament\Resources\ProductHistoryResource\Pages;
use App\Models\ProductHistory;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use BackedEnum;

final class ProductHistoryResource extends Resource
{
    protected static ?string $model = ProductHistory::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('product_history.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return "Products";
    }

    public static function getPluralModelLabel(): string
    {
        return __('product_history.plural');
    }

    public static function getModelLabel(): string
    {
        return __('product_history.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('product_history.basic_information'))
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label(__('product_history.product'))
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('user_id')
                            ->label(__('product_history.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('action')
                            ->label(__('product_history.action'))
                            ->required()
                            ->options(self::actionOptions()),
                        TextInput::make('field_name')
                            ->label(__('product_history.field_name'))
                            ->maxLength(255),
                        TextInput::make('ip_address')
                            ->label(__('product_history.ip_address'))
                            ->maxLength(45)
                            ->helperText(__('product_history.ip_address_help')),
                        TextInput::make('user_agent')
                            ->label(__('product_history.user_agent'))
                            ->maxLength(500)
                            ->columnSpanFull(),
                        DateTimePicker::make('created_at')
                            ->label(__('product_history.created_at'))
                            ->seconds(false)
                            ->required(),
                        DateTimePicker::make('updated_at')
                            ->label(__('product_history.updated_at'))
                            ->seconds(false),
                    ]),
                Section::make(__('product_history.change_values'))
                    ->columns(2)
                    ->schema([
                        Textarea::make('old_value')
                            ->label(__('product_history.old_value'))
                            ->rows(4)
                            ->helperText(__('product_history.old_value_help'))
                            ->formatStateUsing(fn($state) => self::encodeJsonForTextarea($state))
                            ->dehydrateStateUsing(fn(?string $state) => self::decodeJsonFromTextarea($state)),
                        Textarea::make('new_value')
                            ->label(__('product_history.new_value'))
                            ->rows(4)
                            ->helperText(__('product_history.new_value_help'))
                            ->formatStateUsing(fn($state) => self::encodeJsonForTextarea($state))
                            ->dehydrateStateUsing(fn(?string $state) => self::decodeJsonFromTextarea($state)),
                        Textarea::make('description')
                            ->label(__('product_history.description'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('product_history.metadata'))
                    ->schema([
                        KeyValue::make('metadata')
                            ->label(__('product_history.metadata'))
                            ->keyLabel(__('translations.metadata_key'))
                            ->valueLabel(__('translations.metadata_value'))
                            ->helperText(__('admin.system_settings.metadata_help'))
                            ->addActionLabel(__('translations.metadata_key'))
                            ->reorderable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('product_history.product'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('action')
                    ->label(__('product_history.action'))
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => self::actionOptions()[$state] ?? $state)
                    ->color(fn(string $state): string => self::actionColor($state)),
                TextColumn::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('old_value')
                    ->label(__('product_history.old_value'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state): string => self::formatValueForTable($state))
                    ->limit(60),
                TextColumn::make('new_value')
                    ->label(__('product_history.new_value'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state): string => self::formatValueForTable($state))
                    ->limit(60),
                TextColumn::make('user.name')
                    ->label(__('product_history.user'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip_address')
                    ->label(__('product_history.ip_address'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('product_history.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_history.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('action')
                    ->label(__('product_history.action'))
                    ->options(self::actionOptions()),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label(__('product_history.filters.from')),
                        DatePicker::make('until')->label(__('product_history.filters.until')),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (!empty($data['from'])) {
                            $indicators[] = __('product_history.filters.from_indicator', ['date' => Carbon::parse($data['from'])->toFormattedDateString()]);
                        }
                        if (!empty($data['until'])) {
                            $indicators[] = __('product_history.filters.until_indicator', ['date' => Carbon::parse($data['until'])->toFormattedDateString()]);
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): void {
                        $query
                            ->when($data['from'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            ProductHistoryStatsWidget::class,
            RecentProductChangesWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductHistories::route('/'),
            'create' => Pages\CreateProductHistory::route('/create'),
            'view' => Pages\ViewProductHistory::route('/{record}'),
            'edit' => Pages\EditProductHistory::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function actionOptions(): array
    {
        return [
            'created' => __('product_history.actions.created'),
            'updated' => __('product_history.actions.updated'),
            'deleted' => __('product_history.actions.deleted'),
            'restored' => __('product_history.actions.restored'),
            'price_changed' => __('product_history.actions.price_changed'),
            'stock_updated' => __('product_history.actions.stock_updated'),
            'status_changed' => __('product_history.actions.status_changed'),
            'category_changed' => __('product_history.actions.category_changed'),
            'image_changed' => __('product_history.actions.image_changed'),
            'custom' => __('product_history.actions.custom'),
        ];
    }

    private static function actionColor(string $action): string
    {
        return match ($action) {
            'created' => 'success',
            'updated', 'category_changed', 'image_changed', 'custom' => 'primary',
            'deleted' => 'danger',
            'restored' => 'gray',
            'price_changed' => 'warning',
            'stock_updated' => 'info',
            'status_changed' => 'purple',
            default => 'secondary',
        };
    }

    private static function encodeJsonForTextarea(mixed $value): ?string
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

    private static function decodeJsonFromTextarea(?string $value): mixed
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

        return $trimmed;
    }

    private static function formatValueForTable(mixed $value): string
    {
        if ($value === null) {
            return __('admin.common.none');
        }

        if (is_bool($value)) {
            return $value ? __('admin.common.yes') : __('admin.common.no');
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $flattened = Arr::flatten($value);
            if (count($flattened) === 1) {
                $single = reset($flattened);
                return is_scalar($single) ? (string) $single : json_encode($single, JSON_UNESCAPED_UNICODE);
            }
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
