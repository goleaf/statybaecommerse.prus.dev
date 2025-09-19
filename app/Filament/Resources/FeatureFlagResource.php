<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;

use App\Filament\Resources\FeatureFlagResource\Pages;
use App\Models\FeatureFlag;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * FeatureFlagResource
 *
 * Filament v4 resource for FeatureFlag management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('feature_flags.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('feature_flags.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('feature_flags.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
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
                            DateTimePicker::make('starts_at')
                                ->label(__('feature_flags.starts_at'))
                                ->nullable(),
                            DateTimePicker::make('ends_at')
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
                                    'local' => 'Local',
                                    'staging' => 'Staging',
                                    'production' => 'Production',
                                ])
                                ->nullable(),
                            Select::make('category')
                                ->label(__('feature_flags.category'))
                                ->options([
                                    'ui' => 'UI/UX',
                                    'performance' => 'Performance',
                                    'security' => 'Security',
                                    'analytics' => 'Analytics',
                                    'payment' => 'Payment',
                                    'shipping' => 'Shipping',
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
                    ->color(fn (string $state): string => match ($state) {
                        'ui' => 'info',
                        'performance' => 'success',
                        'security' => 'danger',
                        'analytics' => 'warning',
                        'payment' => 'primary',
                        'shipping' => 'secondary',
                        default => 'gray',
                    }),
                TextColumn::make('environment')
                    ->label(__('feature_flags.environment'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'local' => 'gray',
                        'staging' => 'warning',
                        'production' => 'success',
                        default => 'gray',
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
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label(__('feature_flags.category'))
                    ->options([
                        'ui' => 'UI/UX',
                        'performance' => 'Performance',
                        'security' => 'Security',
                        'analytics' => 'Analytics',
                        'payment' => 'Payment',
                        'shipping' => 'Shipping',
                    ]),
                SelectFilter::make('environment')
                    ->label(__('feature_flags.environment'))
                    ->options([
                        'local' => 'Local',
                        'staging' => 'Staging',
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
                DeleteAction::make(),
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
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Handle getPages functionality with proper error handling.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatureFlags::route('/'),
            'create' => Pages\CreateFeatureFlag::route('/create'),
            'view' => Pages\ViewFeatureFlag::route('/{record}'),
            'edit' => Pages\EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
