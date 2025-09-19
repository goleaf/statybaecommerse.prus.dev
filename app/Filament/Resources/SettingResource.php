<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * SettingResource
 *
 * Filament v4 resource for Setting management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'key';
    protected static ?string $navigationGroup = NavigationGroup::Settings;


    protected static $navigationGroup = NavigationGroup::Settings;

    public static function getNavigationLabel(): string
    {
        return __('admin.settings.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.settings.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.settings.model_label');
    }

    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.settings.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('key')
                                    ->label(__('admin.settings.key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Setting::class, 'key', ignoreRecord: true)
                                    ->rules(['alpha_dash']),

                                TextInput::make('display_name')
                                    ->label(__('admin.settings.display_name'))
                                    ->required()
                                    ->maxLength(255),

                                Select::make('type')
                                    ->label(__('admin.settings.type'))
                                    ->options([
                                        'string' => __('admin.settings.types.string'),
                                        'number' => __('admin.settings.types.number'),
                                        'boolean' => __('admin.settings.types.boolean'),
                                        'json' => __('admin.settings.types.json'),
                                        'array' => __('admin.settings.types.array'),
                                    ])
                                    ->required()
                                    ->default('string')
                                    ->live(),

                                TextInput::make('group')
                                    ->label(__('admin.settings.group'))
                                    ->maxLength(100)
                                    ->default('general'),
                            ]),

                        Textarea::make('description')
                            ->label(__('admin.settings.description'))
                            ->maxLength(1000)
                            ->rows(3),
                    ]),

                SchemaSection::make(__('admin.settings.value'))
                    ->schema([
                        TextInput::make('value')
                            ->label(__('admin.settings.value'))
                            ->required()
                            ->maxLength(1000)
                            ->visible(fn (callable $get) => in_array($get('type'), ['string', 'number'])),

                        Toggle::make('value')
                            ->label(__('admin.settings.value'))
                            ->visible(fn (callable $get) => $get('type') === 'boolean'),

                        Textarea::make('value')
                            ->label(__('admin.settings.value'))
                            ->rows(5)
                            ->visible(fn (callable $get) => in_array($get('type'), ['json', 'array']))
                            ->helperText(__('admin.settings.value_json_help')),
                    ]),

                SchemaSection::make(__('admin.settings.permissions'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label(__('admin.settings.is_public'))
                                    ->default(false),

                                Toggle::make('is_required')
                                    ->label(__('admin.settings.is_required'))
                                    ->default(false),

                                Toggle::make('is_encrypted')
                                    ->label(__('admin.settings.is_encrypted'))
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('display_name')
                    ->label(__('admin.settings.display_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('admin.settings.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'success',
                        'number' => 'info',
                        'boolean' => 'warning',
                        'json' => 'danger',
                        'array' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('group')
                    ->label(__('admin.settings.group'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('value')
                    ->label(__('admin.settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                IconColumn::make('is_public')
                    ->label(__('admin.settings.is_public'))
                    ->boolean(),

                IconColumn::make('is_required')
                    ->label(__('admin.settings.is_required'))
                    ->boolean(),

                IconColumn::make('is_encrypted')
                    ->label(__('admin.settings.is_encrypted'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.settings.type'))
                    ->options([
                        'string' => __('admin.settings.types.string'),
                        'number' => __('admin.settings.types.number'),
                        'boolean' => __('admin.settings.types.boolean'),
                        'json' => __('admin.settings.types.json'),
                        'array' => __('admin.settings.types.array'),
                    ]),

                SelectFilter::make('group')
                    ->label(__('admin.settings.group'))
                    ->options(function () {
                        return Setting::distinct()
                            ->pluck('group', 'group')
                            ->filter()
                            ->toArray();
                    }),

                TernaryFilter::make('is_public')
                    ->label(__('admin.settings.is_public')),

                TernaryFilter::make('is_required')
                    ->label(__('admin.settings.is_required')),

                TernaryFilter::make('is_encrypted')
                    ->label(__('admin.settings.is_encrypted')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'view' => Pages\ViewSetting::route('/{record}'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
