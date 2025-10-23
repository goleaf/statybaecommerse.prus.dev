<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingHistories\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

final class SystemSettingHistoryForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                SchemaSection::make(__('admin.system_setting_histories.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Select::make('system_setting_id')
                                    ->label(__('admin.system_setting_histories.system_setting'))
                                    ->relationship('systemSetting', 'key', fn ($query) => $query->withoutGlobalScopes())
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->searchable()
                                    ->preload(),
                                Select::make('changed_by')
                                    ->label(__('admin.system_setting_histories.changed_by'))
                                    ->relationship('user', 'name')
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('change_reason')
                                    ->label(__('admin.system_setting_histories.change_reason'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('ip_address')
                                    ->label(__('admin.system_setting_histories.ip_address'))
                                    ->ip()
                                    ->maxLength(45),
                                TextInput::make('user_agent')
                                    ->label(__('admin.system_setting_histories.user_agent'))
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                            ]),
                        Textarea::make('old_value')
                            ->label(__('admin.system_setting_histories.old_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.old_value_help'))
                            ->columnSpanFull(),
                        Textarea::make('new_value')
                            ->label(__('admin.system_setting_histories.new_value'))
                            ->rows(3)
                            ->helperText(__('admin.system_setting_histories.new_value_help'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
