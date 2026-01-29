<?php

declare(strict_types=1);

namespace App\Filament\Resources\UiTranslations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UiTranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.system_settings.normal_setting_translations.basic_information'))
                    ->schema([
                        TextInput::make('key')
                            ->label(__('messages.Key'))
                            ->required()
                            ->maxLength(255),
                        Select::make('locale')
                            ->label(__('messages.locale'))
                            ->options([
                                'lt' => 'Lithuanian',
                                'en' => 'English',
                                'de' => 'German',
                                'ru' => 'Russian',
                            ])
                            ->required(),
                        TextInput::make('group')
                            ->label(__('messages.Group'))
                            ->maxLength(255),
                        Textarea::make('value')
                            ->label(__('messages.Value'))
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
