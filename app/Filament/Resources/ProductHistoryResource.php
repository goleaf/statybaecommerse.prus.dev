<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductHistoryResource\Pages;
use App\Filament\Resources\ProductHistoryResource\Widgets\ProductHistoryStatsWidget;
use App\Filament\Resources\ProductHistoryResource\Widgets\RecentProductChangesWidget;
use App\Models\ProductHistory;
use App\Support\Filament\Components\Flatpickr; // Custom Flatpickr helper keeps date filters consistent with the admin UI
use BackedEnum;
use DateTimeInterface;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use EncoreDigitalGroup\Filament\Helpers\InputTypes\Select\Select as SelectInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use UnitEnum;

final class ProductHistoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductHistory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::Products;

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make(__('product_history.basic_information'))
                ->columns(2)
                ->schema([
                    SelectInput::make('product_id', __('product_history.product'))
                        ->relationship('product', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    SelectInput::make('user_id', __('product_history.user'))
                        ->relationship('user', 'name')
                        ->preload(),
                    SelectInput::make('action', __('product_history.action'))
                        ->required()
                        ->options(self::actionOptions()),
                    SearchableInput::make('field_name')
                        ->label(__('product_history.field_name'))
                        ->maxLength(255)
                        ->searchUsing(fn (string $search): array => self::searchFieldNames($search))
                        ->options(fn (): array => self::fieldNameOptions()),
                ]),
            Section::make(__('product_history.details'))
                ->columns(2)
                ->schema([
                    Textarea::make('old_value')
                        ->label(__('product_history.old_value'))
                        ->rows(3)
                        ->formatStateUsing(fn (mixed $state): ?string => self::encodeJsonForTextarea($state))
                        ->dehydrateStateUsing(fn (?string $state): mixed => self::decodeJsonFromTextarea($state)),
                    Textarea::make('new_value')
                        ->label(__('product_history.new_value'))
                        ->rows(3)
                        ->formatStateUsing(fn (mixed $state): ?string => self::encodeJsonForTextarea($state))
                        ->dehydrateStateUsing(fn (?string $state): mixed => self::decodeJsonFromTextarea($state)),
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
                    ->color(fn (string $state): string => self::actionColor($state))
                    ->sortable(),
                TextColumn::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->toggleable(),
                TextColumn::make('old_value')
                    ->label(__('product_history.old_value'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (mixed $state): string => self::formatValueForTable($state))
                    ->wrap(),
                TextColumn::make('new_value')
                    ->label(__('product_history.new_value'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn (mixed $state): string => self::formatValueForTable($state))
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('product_history.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label(__('product_history.action'))
                    ->options(self::actionOptions()),
                SelectFilter::make('product_id')
                    ->label(__('product_history.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('user_id')
                    ->label(__('product_history.user'))
                    ->relationship('user', 'name')
                    ->preload(),
                Filter::make('field_name')
                    ->label(__('product_history.field_name'))
                    ->form([
                        SearchableInput::make('field_name')
                            ->label(__('product_history.field_name'))
                            ->maxLength(255)
                            ->searchUsing(fn (string $search): array => self::fieldNameSuggestions($search))
                            ->options(fn (): array => self::fieldNameSuggestions()),
                    ])
                    ->indicateUsing(fn (array $data): array => filled($data['field_name'] ?? null)
                        ? [__('product_history.field_name') . ': ' . $data['field_name']]
                        : [])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            filled($data['field_name'] ?? null),
                            fn (Builder $query, string $fieldName): Builder => $query->where('field_name', $fieldName),
                        );
                    }),
                Filter::make('date')
                    ->label(__('product_history.date'))
                    ->form([
                        Flatpickr::makeDate('from')
                            ->label(__('product_history.from')),
                        Flatpickr::makeDate('until')
                            ->label(__('product_history.until')),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = __('product_history.from') . ': ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = __('product_history.until') . ': ' . $data['until'];
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $from = self::formatDateFilterValue($data['from'] ?? null);
                        $until = self::formatDateFilterValue($data['until'] ?? null);

                        return $query
                            ->when(
                                $from,
                                static fn (Builder $query) => $query->whereDate('created_at', '>=', $from)
                            )
                            ->when(
                                $until,
                                static fn (Builder $query) => $query->whereDate('created_at', '<=', $until)
                            );
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
            'index'  => Pages\ListProductHistories::route('/'),
            'create' => Pages\CreateProductHistory::route('/create'),
            'view'   => Pages\ViewProductHistory::route('/{record}'),
            'edit'   => Pages\EditProductHistory::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function actionOptions(): array
    {
        return [
            'created'          => __('product_history.actions.created'),
            'updated'          => __('product_history.actions.updated'),
            'deleted'          => __('product_history.actions.deleted'),
            'restored'         => __('product_history.actions.restored'),
            'price_changed'    => __('product_history.actions.price_changed'),
            'stock_updated'    => __('product_history.actions.stock_updated'),
            'status_changed'   => __('product_history.actions.status_changed'),
            'category_changed' => __('product_history.actions.category_changed'),
            'image_changed'    => __('product_history.actions.image_changed'),
            'custom'           => __('product_history.actions.custom'),
        ];
    }

    private static function actionColor(string $action): string
    {
        return match ($action) {
            'created' => 'success',
            'updated', 'category_changed', 'image_changed', 'custom' => 'primary',
            'deleted'       => 'danger',
            'restored'      => 'gray',
            'price_changed' => 'warning',
            'stock_updated', 'stock_changed' => 'info',
            'status_changed' => 'purple',
            default          => 'secondary',
        };
    }

    /**
     * @return array<int, string>
     */
    private static function fieldNameSuggestions(?string $search = null): array
    {
        return ProductHistory::query()
            ->select('field_name')
            ->whereNotNull('field_name')
            ->when($search !== null, fn (Builder $query): Builder => $query->where('field_name', 'like', "%{$search}%"))
            ->distinct()
            ->orderBy('field_name')
            ->limit(20)
            ->pluck('field_name')
            ->all();
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

        $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return is_string($encoded) ? $encoded : null;
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
        if (is_scalar($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            $flattened = Arr::flatten($value);
            if (count($flattened) === 1) {
                $single = reset($flattened);

                if (is_scalar($single)) {
                    return (string) $single;
                }

                $encodedSingle = json_encode($single, JSON_UNESCAPED_UNICODE);

                return is_string($encodedSingle) ? $encodedSingle : '';
            }
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return is_string($encoded) ? $encoded : '';
    }

    /**
     * @return array<string, string>
     */
    private static function fieldNameOptions(): array
    {
        return ProductHistory::query()
            ->select('field_name')
            ->whereNotNull('field_name')
            ->distinct()
            ->orderBy('field_name')
            ->pluck('field_name')
            ->mapWithKeys(static fn (mixed $field): array => [
                (string) $field => self::fieldLabel((string) $field),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function searchFieldNames(string $search): array
    {
        $term = trim($search);

        if ($term === '') {
            return [];
        }

        return ProductHistory::query()
            ->select('field_name')
            ->whereNotNull('field_name')
            ->where('field_name', 'like', "%{$term}%")
            ->distinct()
            ->orderBy('field_name')
            ->limit(20)
            ->pluck('field_name')
            ->map(static fn (mixed $field): string => self::fieldLabel((string) $field))
            ->values()
            ->all();
    }

    private static function fieldLabel(string $field): string
    {
        $translationKey = 'admin.product_history.fields.' . $field;
        $translated = __($translationKey);

        return $translated === $translationKey ? $field : $translated;
    }

    private static function formatDateFilterValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }
}
