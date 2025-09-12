<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\NormalSettingResource\Pages;
use App\Models\NormalSetting;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Actions as Actions;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class NormalSettingResource extends Resource
{
    protected static ?string $model = NormalSetting::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::System;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.enhanced_settings');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.enhanced_settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.enhanced_settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('enhanced_settings.setting_information'))
                    ->components([
                        Forms\Components\Select::make('group')
                            ->label(__('enhanced_settings.group'))
                            ->options([
                                'general' => __('enhanced_settings.groups.general'),
                                'ecommerce' => __('enhanced_settings.groups.ecommerce'),
                                'email' => __('enhanced_settings.groups.email'),
                                'payment' => __('enhanced_settings.groups.payment'),
                                'shipping' => __('enhanced_settings.groups.shipping'),
                                'seo' => __('enhanced_settings.groups.seo'),
                                'security' => __('enhanced_settings.groups.security'),
                                'api' => __('enhanced_settings.groups.api'),
                                'appearance' => __('enhanced_settings.groups.appearance'),
                                'notifications' => __('enhanced_settings.groups.notifications'),
                            ])
                            ->required()
                            ->default('general'),
                        Forms\Components\TextInput::make('key')
                            ->label(__('enhanced_settings.key'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->regex('/^[a-z0-9_\.]+$/')
                            ->helperText(__('enhanced_settings.help.key')),
                        Forms\Components\Select::make('type')
                            ->label(__('enhanced_settings.type'))
                            ->options([
                                'text' => __('enhanced_settings.types.text'),
                                'textarea' => __('enhanced_settings.types.textarea'),
                                'number' => __('enhanced_settings.types.number'),
                                'boolean' => __('enhanced_settings.types.boolean'),
                                'json' => __('enhanced_settings.types.json'),
                                'array' => __('enhanced_settings.types.array'),
                                'select' => __('enhanced_settings.types.select'),
                                'file' => __('enhanced_settings.types.file'),
                                'color' => __('enhanced_settings.types.color'),
                                'date' => __('enhanced_settings.types.date'),
                                'datetime' => __('enhanced_settings.types.datetime'),
                            ])
                            ->required()
                            ->default('text')
                            ->live(),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
                Section::make(__('Value Configuration'))
                    ->components([
                        Forms\Components\TextInput::make('value')
                            ->label(__('Value'))
                            ->visible(fn(Forms\Get $get) => in_array($get('type'), ['text', 'number']))
                            ->required()
                            ->maxLength(1000),
                        Forms\Components\Textarea::make('value')
                            ->label(__('Value'))
                            ->visible(fn(Forms\Get $get) => $get('type') === 'textarea')
                            ->rows(3),
                        Forms\Components\KeyValue::make('value')
                            ->label(__('Value'))
                            ->visible(fn(Forms\Get $get) => in_array($get('type'), ['json', 'array']))
                            ->keyLabel(__('Key'))
                            ->valueLabel(__('Value')),
                        Forms\Components\Select::make('value')
                            ->label(__('Value'))
                            ->visible(fn(Forms\Get $get) => $get('type') === 'select')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ]),
                    ])
                    ->columns(2),
                Section::make(__('Translation'))
                    ->components([
                        Forms\Components\TextInput::make('locale')
                            ->label(__('Locale'))
                            ->default(app()->getLocale())
                            ->maxLength(10),
                        Forms\Components\TextInput::make('description')
                            ->label(__('Description'))
                            ->maxLength(1000),
                    ]),
                Section::make(__('Advanced Options'))
                    ->components([
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('Is Public'))
                            ->helperText(__('Public settings can be accessed from frontend')),
                        Forms\Components\Toggle::make('is_encrypted')
                            ->label(__('Is Encrypted'))
                            ->helperText(__('Sensitive settings will be encrypted in database')),
                        Forms\Components\KeyValue::make('validation_rules')
                            ->label(__('Validation Rules'))
                            ->helperText(__('Laravel validation rules in key-value format')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label(__('Group'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(__('Key'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'boolean' => 'success',
                        'number' => 'info',
                        'json', 'array' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('Value'))
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('Public'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_encrypted')
                    ->label(__('Encrypted'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label(__('Group'))
                    ->options(fn() => NormalSetting::distinct()->pluck('group', 'group')->filter(fn($label) => filled($label))->toArray()),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'text' => __('Text'),
                        'number' => __('Number'),
                        'boolean' => __('Boolean'),
                        'json' => __('JSON'),
                        'array' => __('Array'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('Is Public')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group', 'asc')
            ->groups([
                Tables\Grouping\Group::make('group')
                    ->label(__('Group')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNormalSettings::route('/'),
            'create' => Pages\CreateNormalSetting::route('/create'),
            'view' => Pages\ViewNormalSetting::route('/{record}'),
            'edit' => Pages\EditNormalSetting::route('/{record}/edit'),
        ];
    }
}
