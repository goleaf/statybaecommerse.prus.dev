<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingsResource\Schemas;

use App\Models\SystemSetting;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SystemSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.system_settings.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('key')
                                ->label(__('messages.key'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255)
                                ->disabled(fn (?SystemSetting $record) => $record !== null),
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('type')
                                ->label(__('messages.type'))
                                ->required()
                                ->options([
                                    'string'   => __('admin.normal_settings.types.string'),
                                    'text'     => __('admin.normal_settings.types.text'),
                                    'integer'  => __('admin.normal_settings.types.integer'),
                                    'float'    => __('admin.normal_settings.types.float'),
                                    'boolean'  => __('admin.normal_settings.types.boolean'),
                                    'json'     => __('admin.normal_settings.types.json'),
                                    'array'    => __('admin.normal_settings.types.array'),
                                    'color'    => __('admin.normal_settings.types.color'),
                                    'date'     => __('admin.normal_settings.types.date'),
                                    'datetime' => __('admin.normal_settings.types.datetime'),
                                ])
                                ->live(),
                            Select::make('category_id')
                                ->label(__('messages.category'))
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')->required(),
                                    TextInput::make('slug')->required()->unique('system_setting_categories', 'slug'),
                                    Textarea::make('description'),
                                ]),
                        ]),
                    TextInput::make('group')
                        ->label(__('messages.group'))
                        ->required()
                        ->maxLength(255),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                ]),

            Section::make(__('admin.system_settings.value_configuration'))
                ->schema([
                    TextInput::make('value')
                        ->label(__('messages.value'))
                        ->hidden(fn (Get $get) => in_array($get('type'), ['text', 'json', 'array'])),
                    Textarea::make('value')
                        ->label(__('messages.value'))
                        ->visible(fn (Get $get) => $get('type') === 'text'),
                    KeyValue::make('value')
                        ->label(__('messages.value'))
                        ->visible(fn (Get $get) => in_array($get('type'), ['json', 'array'])),

                    TextInput::make('default_value')
                        ->label(__('messages.default_value')),
                ]),

            Section::make(__('admin.system_settings.visibility_and_security'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Toggle::make('is_public')
                                ->label(__('messages.is_public')),
                            Toggle::make('is_required')
                                ->label(__('messages.is_required')),
                            Toggle::make('is_encrypted')
                                ->label(__('messages.is_encrypted')),
                            Toggle::make('is_readonly')
                                ->label(__('messages.is_readonly')),
                            Toggle::make('is_active')
                                ->label(__('messages.is_active'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }
}
