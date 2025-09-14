<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * ZoneResource
 * 
 * Filament resource for admin panel management.
 */
class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    /** @var BackedEnum|string|null */
    protected static $navigationIcon = 'heroicon-o-globe-alt';

    /** @var BackedEnum|string|null */

    /**
     * @var UnitEnum|string|null
     */
    /** @var UnitEnum|string|null */
    protected static $navigationGroup = NavigationGroup::Content;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Zones';

    protected static ?string $modelLabel = 'Zone';

    protected static ?string $pluralModelLabel = 'Zones';

    public static function form(Schema $schema): Schema {
        return $schema->components([
                Forms\Components\Section::make('Basic Information')
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('zones.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('zones.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('code')
                            ->label(__('zones.code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label(__('zones.description'))
                            ->rows(3),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Configuration')
                    ->components([
                        Forms\Components\Select::make('currency_id')
                            ->label(__('zones.currency'))
                            ->relationship('currency', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('type')
                            ->label(__('zones.type'))
                            ->options([
                                'shipping' => __('zones.type_shipping'),
                                'tax' => __('zones.type_tax'),
                                'payment' => __('zones.type_payment'),
                                'delivery' => __('zones.type_delivery'),
                                'general' => __('zones.type_general'),
                            ])
                            ->required()
                            ->default('shipping'),
                        Forms\Components\TextInput::make('tax_rate')
                            ->label(__('zones.tax_rate'))
                            ->numeric()
                            ->step(0.0001)
                            ->suffix('%')
                            ->default(0)
                            ->helperText(__('zones.tax_rate_help')),
                        Forms\Components\TextInput::make('shipping_rate')
                            ->label(__('zones.shipping_rate'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->default(0)
                            ->helperText(__('zones.shipping_rate_help')),
                        Forms\Components\TextInput::make('min_order_amount')
                            ->label(__('zones.min_order_amount'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText(__('zones.min_order_amount_help')),
                        Forms\Components\TextInput::make('max_order_amount')
                            ->label(__('zones.max_order_amount'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText(__('zones.max_order_amount_help')),
                        Forms\Components\TextInput::make('free_shipping_threshold')
                            ->label(__('zones.free_shipping_threshold'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->helperText(__('zones.free_shipping_threshold_help')),
                        Forms\Components\TextInput::make('priority')
                            ->label(__('zones.priority'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('zones.priority_help')),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('zones.sort_order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Status')
                    ->components([
                        Forms\Components\Toggle::make('is_enabled')
                            ->label(__('zones.is_enabled'))
                            ->default(true)
                            ->helperText(__('zones.is_enabled_help')),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('zones.is_active'))
                            ->default(true)
                            ->helperText(__('zones.is_active_help')),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('zones.is_default'))
                            ->default(false)
                            ->helperText(__('zones.is_default_help')),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Countries')
                    ->components([
                        Forms\Components\Select::make('countries')
                            ->label(__('zones.countries'))
                            ->relationship('countries', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),
                Forms\Components\Section::make('Translations')
                    ->components([
                        Forms\Components\Repeater::make('translations')
                            ->label(__('zones.translations'))
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->label(__('zones.locale'))
                                    ->options([
                                        'lt' => 'Lithuanian',
                                        'en' => 'English',
                                        'de' => 'German',
                                        'ru' => 'Russian',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->label(__('zones.name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('description')
                                    ->label(__('zones.description'))
                                    ->rows(2),
                                Forms\Components\Textarea::make('short_description')
                                    ->label(__('zones.short_description'))
                                    ->rows(2),
                                Forms\Components\RichEditor::make('long_description')
                                    ->label(__('zones.long_description'))
                                    ->rows(4),
                                Forms\Components\TextInput::make('meta_title')
                                    ->label(__('zones.meta_title'))
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('meta_description')
                                    ->label(__('zones.meta_description'))
                                    ->rows(2)
                                    ->maxLength(500),
                                Forms\Components\TagsInput::make('meta_keywords')
                                    ->label(__('zones.meta_keywords'))
                                    ->helperText(__('zones.meta_keywords_help')),
                            ])
                            ->columns(2)
                            ->addActionLabel(__('zones.add_translation'))
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null),
                    ]),
                Forms\Components\Section::make('Metadata')
                    ->components([
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('zones.metadata'))
                            ->keyLabel(__('zones.key'))
                            ->valueLabel(__('zones.value')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('zones.code'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('zones.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('zones.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'shipping' => 'info',
                        'tax' => 'warning',
                        'payment' => 'success',
                        'delivery' => 'primary',
                        'general' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'shipping' => __('zones.type_shipping'),
                        'tax' => __('zones.type_tax'),
                        'payment' => __('zones.type_payment'),
                        'delivery' => __('zones.type_delivery'),
                        'general' => __('zones.type_general'),
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency.name')
                    ->label(__('zones.currency'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_rate')
                    ->label(__('zones.tax_rate'))
                    ->formatStateUsing(fn (string $state): string => $state.'%')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('shipping_rate')
                    ->label(__('zones.shipping_rate'))
                    ->formatStateUsing(fn (string $state): string => '€'.$state)
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('free_shipping_threshold')
                    ->label(__('zones.free_shipping_threshold'))
                    ->formatStateUsing(fn (?string $state): string => $state ? '€'.$state : '-')
                    ->sortable()
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('countries_count')
                    ->label(__('zones.countries_count'))
                    ->counts('countries')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('priority')
                    ->label(__('zones.priority'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('zones.is_enabled'))
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('zones.is_active'))
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label(__('zones.is_default'))
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('zones.sort_order'))
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('zones.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('zones.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label(__('zones.is_enabled')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('zones.is_active')),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label(__('zones.is_default')),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('zones.type'))
                    ->options([
                        'shipping' => __('zones.type_shipping'),
                        'tax' => __('zones.type_tax'),
                        'payment' => __('zones.type_payment'),
                        'delivery' => __('zones.type_delivery'),
                        'general' => __('zones.type_general'),
                    ]),
                Tables\Filters\SelectFilter::make('currency')
                    ->label(__('zones.currency'))
                    ->relationship('currency', 'name'),
                Tables\Filters\Filter::make('has_countries')
                    ->label(__('zones.has_countries'))
                    ->query(fn (Builder $query): Builder => $query->has('countries')),
                Tables\Filters\Filter::make('free_shipping_available')
                    ->label(__('zones.free_shipping_available'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('free_shipping_threshold')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label(__('zones.duplicate_zone'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Zone $record) {
                        $newZone = $record->replicate();
                        $newZone->name = $record->name.' (Copy)';
                        $newZone->code = $record->code.'_copy';
                        $newZone->is_default = false;
                        $newZone->save();

                        // Copy translations
                        foreach ($record->translations as $translation) {
                            $newTranslation = $translation->replicate();
                            $newTranslation->zone_id = $newZone->id;
                            $newTranslation->save();
                        }

                        // Copy countries
                        $newZone->countries()->attach($record->countries->pluck('id'));

                        return redirect()->route('filament.admin.resources.zones.edit', $newZone);
                    })
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable')
                        ->label(__('zones.bulk_enable'))
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('disable')
                        ->label(__('zones.bulk_disable'))
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label(__('zones.bulk_activate'))
                        ->icon('heroicon-o-play')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(__('zones.bulk_deactivate'))
                        ->icon('heroicon-o-pause')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CountriesRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\PriceListsRelationManager::class,
            RelationManagers\DiscountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
