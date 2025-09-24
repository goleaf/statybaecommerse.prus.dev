<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NormalSettingResource\Pages;
use App\Models\NormalSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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
use UnitEnum;

final class NormalSettingResource extends Resource
{
    protected static ?string $model = NormalSetting::class;

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'key';

    public static function getModelLabel(): string
    {
        return __('normal_settings.single');
    }

    public static function getPluralModelLabel(): string
    {
        return __('normal_settings.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('normal_settings.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make(__('normal_settings.tabs'))
                ->tabs([
                    Tab::make(__('normal_settings.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TextInput::make('key')
                                ->label(__('normal_settings.key'))
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                            TextInput::make('value')
                                ->label(__('normal_settings.value'))
                                ->required()
                                ->maxLength(1000),
                            Textarea::make('description')
                                ->label(__('normal_settings.description'))
                                ->maxLength(500)
                                ->rows(3),
                            Select::make('type')
                                ->label(__('normal_settings.type'))
                                ->options([
                                    'string' => __('normal_settings.types.string'),
                                    'integer' => __('normal_settings.types.integer'),
                                    'boolean' => __('normal_settings.types.boolean'),
                                    'array' => __('normal_settings.types.array'),
                                    'json' => __('normal_settings.types.json'),
                                ])
                                ->required()
                                ->native(false),
                        ]),
                    Tab::make(__('normal_settings.settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Toggle::make('is_public')
                                ->label(__('normal_settings.is_public'))
                                ->helperText(__('normal_settings.is_public_help')),
                            Toggle::make('is_encrypted')
                                ->label(__('normal_settings.is_encrypted'))
                                ->helperText(__('normal_settings.is_encrypted_help')),
                            Toggle::make('is_active')
                                ->label(__('normal_settings.is_active'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('normal_settings.key'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('value')
                    ->label(__('normal_settings.value'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('type')
                    ->label(__('normal_settings.type'))
                    ->sortable(),
                IconColumn::make('is_public')
                    ->label(__('normal_settings.is_public'))
                    ->boolean(),
                IconColumn::make('is_encrypted')
                    ->label(__('normal_settings.is_encrypted'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('normal_settings.is_active'))
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('normal_settings.type'))
                    ->options([
                        'string' => __('normal_settings.types.string'),
                        'integer' => __('normal_settings.types.integer'),
                        'boolean' => __('normal_settings.types.boolean'),
                        'array' => __('normal_settings.types.array'),
                        'json' => __('normal_settings.types.json'),
                    ]),
                TernaryFilter::make('is_public')
                    ->label(__('normal_settings.is_public'))
                    ->nullable(),
                TernaryFilter::make('is_encrypted')
                    ->label(__('normal_settings.is_encrypted'))
                    ->nullable(),
                TernaryFilter::make('is_active')
                    ->label(__('normal_settings.is_active'))
                    ->nullable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNormalSettings::route('/'),
            'create' => Pages\CreateNormalSetting::route('/create'),
            'edit' => Pages\EditNormalSetting::route('/{record}/edit'),
        ];
    }
}
