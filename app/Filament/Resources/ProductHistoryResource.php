<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductHistoryResource\Pages;
use App\Filament\Resources\ProductHistoryResource\Widgets\ProductHistoryStatsWidget;
use App\Filament\Resources\ProductHistoryResource\Widgets\RecentProductChangesWidget;
use App\Models\ProductHistory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use UnitEnum;

final class ProductHistoryResource extends Resource
{
    protected static ?string $model = ProductHistory::class;

    /**
     * @var string|\BackedEnum|null
     */
    public static function getNavigationIcon(): \BackedEnum|\Illuminate\Contracts\Support\Htmlable|string|null
    {
        return 'heroicon-o-clock';
    }

    /**
     * @var UnitEnum|string|null
     */
    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Products';
    }

    protected static ?int $navigationSort = 11;

    public static function getNavigationLabel(): string
    {
        return __('product_history.title');
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
        return $schema->schema([
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
                        ->preload(),
                    Select::make('action')
                        ->label(__('product_history.action'))
                        ->required()
                        ->options(self::actionOptions()),
                    TextInput::make('field_name')
                        ->label(__('product_history.field_name'))
                        ->maxLength(255),
                ]),
            Section::make(__('product_history.details'))
                ->columns(2)
                ->schema([
                    TextInput::make('old_value')
                        ->label(__('product_history.old_value'))
                        ->maxLength(255),
                    TextInput::make('new_value')
                        ->label(__('product_history.new_value'))
                        ->maxLength(255),
                    KeyValue::make('meta')
                        ->label(__('product_history.meta'))
                        ->keyLabel(__('product_history.meta_key'))
                        ->valueLabel(__('product_history.meta_value'))
                        ->columnSpanFull(),
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
                TextColumn::make('user.name')
                    ->label(__('product_history.user'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('action')
                    ->label(__('product_history.action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->toggleable(),
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
                SelectFilter::make('user_id')
                    ->label(__('product_history.user'))
                    ->relationship('user', 'name')
                    ->preload(),
                Filter::make('date')
                    ->label(__('product_history.date'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('product_history.from')),
                        DatePicker::make('until')
                            ->label(__('product_history.until')),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = __('product_history.from').': '.$data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = __('product_history.until').': '.$data['until'];
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    }),
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
            return '';
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $trimmed;
    }

    private static function formatValueForTable(mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('admin.common.none');
        }
        if (is_bool($value)) {
            return $value ? __('admin.common.yes') : __('admin.common.no');
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
