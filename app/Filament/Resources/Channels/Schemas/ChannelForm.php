<?php

namespace App\Filament\Resources\Channels\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class ChannelForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('admin.channels.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.channels.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => $context === 'create' ? $set('slug', \Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label(__('admin.channels.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(\App\Models\Channel::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                TextInput::make('code')
                                    ->label(__('admin.channels.code'))
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(\App\Models\Channel::class, 'code', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                                Select::make('type')
                                    ->label(__('admin.channels.type'))
                                    ->options([
                                        'web' => __('admin.channels.types.web'),
                                        'mobile' => __('admin.channels.types.mobile'),
                                        'api' => __('admin.channels.types.api'),
                                        'pos' => __('admin.channels.types.pos'),
                                    ])
                                    ->required()
                                    ->default('web'),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.channels.description'))
                            ->maxLength(1000)
                            ->rows(3),
                    ]),
                Section::make(__('admin.channels.configuration'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label(__('admin.channels.url'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('domain')
                                    ->label(__('admin.channels.domain'))
                                    ->maxLength(255),
                                TextInput::make('timezone')
                                    ->label(__('admin.channels.timezone'))
                                    ->maxLength(50)
                                    ->default('UTC'),
                                TextInput::make('currency_code')
                                    ->label(__('admin.channels.currency_code'))
                                    ->maxLength(3)
                                    ->default('EUR'),
                                TextInput::make('currency_symbol')
                                    ->label(__('admin.channels.currency_symbol'))
                                    ->maxLength(10)
                                    ->default('€'),
                                Select::make('currency_position')
                                    ->label(__('admin.channels.currency_position'))
                                    ->options([
                                        'before' => __('admin.channels.currency_positions.before'),
                                        'after' => __('admin.channels.currency_positions.after'),
                                    ])
                                    ->default('after'),
                            ]),
                    ]),
                Section::make(__('admin.channels.status'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_enabled')
                                    ->label(__('admin.channels.is_enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('admin.channels.is_default'))
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label(__('admin.channels.is_active'))
                                    ->default(true),
                                Toggle::make('ssl_enabled')
                                    ->label(__('admin.channels.ssl_enabled'))
                                    ->default(true),
                                Toggle::make('analytics_enabled')
                                    ->label(__('admin.channels.analytics_enabled'))
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label(__('admin.channels.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                    ]),
            ]);
    }
}
