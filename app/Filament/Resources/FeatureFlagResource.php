<?php

declare(strict_types=1);

namespace App\Filament\Resources;


use Filament\Schemas\Schema;
use App\Filament\Resources\FeatureFlagResource\Pages;
use App\Models\FeatureFlag;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Support\Filament\Components\Flatpickr;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Actions\DeleteAction as TableDeleteAction;
use Filament\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Actions\EditAction as TableEditAction;
use Filament\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

use Filament\Schemas\Schema;
/**
 * FeatureFlagResource
 *
 * Filament v4 resource for FeatureFlag management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class FeatureFlagResource extends Resource
{
    use HasNav;

    

    protected static ?string $model = FeatureFlag::class;

    /**
     * @var string|BackedEnum|null Navigation icon identifier for the resource navigation menu.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
    }

    /**
     * Ensure administrators can query every feature flag regardless of default scopes.
     */
    public static function getEloquentQuery(): Builder
    {
        // Removing the Active and Enabled scopes keeps disabled flags visible for auditing and reactivation.
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
    }

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     */
    public static function getNavigationLabel(): string
    {
        return __('feature_flags.title');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
    }

    /**
     * Extend the base query so administrators can audit inactive and disabled flags.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     */
    public static function getPluralModelLabel(): string
    {
        return __('feature_flags.plural');
    }

    /**
     * Extend the base query to include inactive or disabled feature flags for administrative visibility.
     */
    public static function getEloquentQuery(): Builder
    {
        // Removing the active/enabled scopes ensures administrators can review every feature flag status.
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
            EnabledScope::class,
        ]);
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     */
    public static function getModelLabel(): string
    {
        return __('feature_flags.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     */
    public static function form(Schema $schema): Schema   
    {
        return $schema->schema([
            Section::make(__('feature_flags.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            TextInput::make('name')
                                ->label(__('feature_flags.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('key')
                                ->label(__('feature_flags.key'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->rules(['alpha_dash']),
                        ]),
                    Textarea::make('description')
                        ->label(__('feature_flags.description'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
            Section::make(__('feature_flags.status'))
                ->components([
                    Grid::make(3)
                        ->components([
                            Toggle::make('is_active')
                                ->label(__('feature_flags.is_active'))
                                ->default(true),
                            Toggle::make('is_enabled')
                                ->label(__('feature_flags.is_enabled'))
                                ->default(false),
                            Toggle::make('is_global')
                                ->label(__('feature_flags.is_global'))
                                ->default(false),
                        ]),
                ]),
            Section::make(__('feature_flags.scheduling'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Flatpickr::makeDateTime('starts_at')
                                ->label(__('feature_flags.starts_at'))
                                ->nullable(),
                            Flatpickr::makeDateTime('ends_at')
                                ->label(__('feature_flags.ends_at'))
                                ->nullable(),
                        ]),
                ]),
            Section::make(__('feature_flags.configuration'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('environment')
                                ->label(__('feature_flags.environment'))
                                ->options([
                                    'local'      => 'Local',
                                    'staging'    => 'Staging',
                                    'production' => 'Production',
                                ])
                                ->nullable(),
                            Select::make('category')
                                ->label(__('feature_flags.category'))
                                ->options([
                                    'ui'          => 'UI/UX',
                                    'performance' => 'Performance',
                                    'security'    => 'Security',
                                    'analytics'   => 'Analytics',
                                    'payment'     => 'Payment',
                                    'shipping'    => 'Shipping',
                                ])
                                ->nullable(),
                        ]),
                    TextInput::make('priority')
                        ->label(__('feature_flags.priority'))
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100),
                ]),
            Section::make(__('feature_flags.conditions'))
                ->components([
                    KeyValue::make('conditions')
                        ->label(__('feature_flags.conditions'))
                        ->keyLabel(__('feature_flags.condition_key'))
                        ->valueLabel(__('feature_flags.condition_value'))
                        ->columnSpanFull(),
                ]),
            Section::make('Attribution')
                ->visible(fn (?FeatureFlag $record): bool => $record !== null)
                ->components([
                    Grid::make(2)
                        ->components([
                            Placeholder::make('created_by_display')
                                ->label(__('system.created_by'))
                                ->content(fn (?FeatureFlag $record): string => $record?->created_by_display ?? '—'),
                            Placeholder::make('updated_by_display')
                                ->label(__('system.updated_by'))
                                ->content(fn (?FeatureFlag $record): string => $record?->updated_by_display ?? '—'),
                        ]),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     */
    public static function table(Table $table): Table   
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('feature_flags.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label(__('feature_flags.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('category')
                    ->label(__('feature_flags.category'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'ui'          => 'info',
                        'performance' => 'success',
                        'security'    => 'danger',
                        'analytics'   => 'warning',
                        'payment'     => 'primary',
                        'shipping'    => 'secondary',
                        default       => 'gray',
                    }),
                TextColumn::make('environment')
                    ->label(__('feature_flags.environment'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'local'      => 'gray',
                        'staging'    => 'warning',
                        'production' => 'success',
                        default      => 'gray',
                    }),
                IconColumn::make('is_active')
                    ->label(__('feature_flags.is_active'))
                    ->boolean(),
                IconColumn::make('is_enabled')
                    ->label(__('feature_flags.is_enabled'))
                    ->boolean(),
                IconColumn::make('is_global')
                    ->label(__('feature_flags.is_global'))
                    ->boolean(),
                TextColumn::make('starts_at')
                    ->label(__('feature_flags.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')
                    ->label(__('feature_flags.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('feature_flags.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_by_display')
                    ->label(__('system.created_by'))
                    ->formatStateUsing(fn (FeatureFlag $record): string => $record->created_by_display ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $scopedQuery) use ($search): void {
                            $scopedQuery
                                ->whereHas('creator', fn (Builder $creatorQuery): Builder => $creatorQuery->where('name', 'like', "%{$search}%"))
                                ->orWhere('created_by_name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_by_display')
                    ->label(__('system.updated_by'))
                    ->formatStateUsing(fn (FeatureFlag $record): string => $record->updated_by_display ?? '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $scopedQuery) use ($search): void {
                            $scopedQuery
                                ->whereHas('updater', fn (Builder $updaterQuery): Builder => $updaterQuery->where('name', 'like', "%{$search}%"))
                                ->orWhere('updated_by_name', 'like', "%{$search}%");
                        });
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('feature_flags.category'))
                    ->options([
                        'ui'          => 'UI/UX',
                        'performance' => 'Performance',
                        'security'    => 'Security',
                        'analytics'   => 'Analytics',
                        'payment'     => 'Payment',
                        'shipping'    => 'Shipping',
                    ]),
                SelectFilter::make('environment')
                    ->label(__('feature_flags.environment'))
                    ->options([
                        'local'      => 'Local',
                        'staging'    => 'Staging',
                        'production' => 'Production',
                    ]),
                TernaryFilter::make('is_active')
                    ->label(__('feature_flags.is_active')),
                TernaryFilter::make('is_enabled')
                    ->label(__('feature_flags.is_enabled')),
                TernaryFilter::make('is_global')
                    ->label(__('feature_flags.is_global')),
            ])
            ->actions([
                TableViewAction::make(),
                TableEditAction::make(),
                TableDeleteAction::make(),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Handle getRelations functionality with proper error handling.
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFeatureFlags::route('/'),
            'create' => Pages\CreateFeatureFlag::route('/create'),
            'view'   => Pages\ViewFeatureFlag::route('/{record}'),
            'edit'   => Pages\EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}