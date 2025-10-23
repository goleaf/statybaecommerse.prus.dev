<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\NormalSettingResource\Pages;
use App\Models\NormalSetting;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class NormalSettingResource extends Resource
{
    use HasNav;

    protected static ?string $model = NormalSetting::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'key';

    public static function getModelLabel(): string
    {
        return __('admin.normal_settings.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.normal_settings.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.normal_settings.navigation');
    }

    public static function form(Schema $form): Schema
    {
        return $schema->schema([
            Tabs::make(__('normal_settings.tabs.label'))
                ->tabs([
                    Tab::make(__('admin.normal_settings.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TextInput::make('key')
                                ->label(__('admin.normal_settings.key'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('value')
                                ->label(__('admin.normal_settings.value'))
                                ->required()
                                ->maxLength(1000),
                            Textarea::make('description')
                                ->label(__('admin.normal_settings.description'))
                                ->maxLength(500)
                                ->rows(3),
                            Select::make('type')
                                ->label(__('admin.normal_settings.type'))
                                ->options([
                                    'string'  => __('admin.normal_settings.types.string'),
                                    'integer' => __('admin.normal_settings.types.integer'),
                                    'boolean' => __('admin.normal_settings.types.boolean'),
                                    'array'   => __('admin.normal_settings.types.array'),
                                    'json'    => __('admin.normal_settings.types.json'),
                                ])
                                ->required()
                                ->native(false),
                        ]),
                    Tab::make(__('admin.normal_settings.settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Toggle::make('is_public')
                                ->label(__('admin.normal_settings.is_public'))
                                ->helperText(__('admin.normal_settings.is_public_help')),
                            Toggle::make('is_encrypted')
                                ->label(__('admin.normal_settings.is_encrypted'))
                                ->helperText(__('admin.normal_settings.is_encrypted_help')),
                            Toggle::make('is_active')
                                ->label(__('admin.normal_settings.is_active'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('admin.normal_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->label(__('admin.normal_settings.value'))
                    ->formatStateUsing(static function ($state): string {
                        if (is_array($state) || is_object($state)) {
                            $encoded = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                            if ($encoded !== false) {
                                return $encoded;
                            }

                            return (string) $state;
                        }

                        if (is_bool($state)) {
                            return $state ? 'true' : 'false';
                        }

                        return (string) $state;
                    })
                    ->limit(50)
                    ->copyable()
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (is_array($state) || is_object($state)) {
                            $state = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        } elseif (is_bool($state)) {
                            $state = $state ? 'true' : 'false';
                        } elseif ($state !== null && ! is_string($state)) {
                            $state = (string) $state;
                        }

                        if (! is_string($state)) {
                            return null;
                        }

                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('type')
                    ->label(__('admin.normal_settings.type'))
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label(__('admin.normal_settings.is_public'))
                    ->boolean(),
                IconColumn::make('is_encrypted')
                    ->label(__('admin.normal_settings.is_encrypted'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('admin.normal_settings.is_active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.normal_settings.type'))
                    ->options([
                        'string'  => __('admin.normal_settings.types.string'),
                        'integer' => __('admin.normal_settings.types.integer'),
                        'boolean' => __('admin.normal_settings.types.boolean'),
                        'array'   => __('admin.normal_settings.types.array'),
                        'json'    => __('admin.normal_settings.types.json'),
                    ]),
                TernaryFilter::make('is_public')
                    ->label(__('admin.normal_settings.is_public'))
                    ->nullable(),
                TernaryFilter::make('is_encrypted')
                    ->label(__('admin.normal_settings.is_encrypted'))
                    ->nullable(),
                TernaryFilter::make('is_active')
                    ->label(__('admin.normal_settings.is_active'))
                    ->nullable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNormalSettings::route('/'),
            'create' => Pages\CreateNormalSetting::route('/create'),
            'edit'   => Pages\EditNormalSetting::route('/{record}/edit'),
        ];
    }
}