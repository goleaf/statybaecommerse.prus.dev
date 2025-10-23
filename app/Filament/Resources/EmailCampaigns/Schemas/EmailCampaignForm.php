<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaigns\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

class EmailCampaignForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->components([
                Section::make('Email campaign details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('subject')
                                    ->label('Subject')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('from_email')
                                    ->label('From email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('from_name')
                                    ->label('From name')
                                    ->maxLength(255),
                                TextInput::make('reply_to')
                                    ->label('Reply-to email')
                                    ->email()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('content')
                            ->label('Content')
                            ->columnSpanFull(),
                        Textarea::make('html_content')
                            ->label('HTML content')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('status')
                                    ->label('Status')
                                    ->default('draft')
                                    ->maxLength(50),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Flatpickr::make('scheduled_at')
                                    ->time(true)
                                    ->time24hr(true)
                                    ->seconds(false)
                                    ->format('Y-m-d H:i')
                                    ->label('Scheduled at'),
                                Flatpickr::make('sent_at')
                                    ->time(true)
                                    ->time24hr(true)
                                    ->seconds(false)
                                    ->format('Y-m-d H:i')
                                    ->label('Sent at'),
                            ]),
                        Flatpickr::make('completed_at')
                            ->time(true)
                            ->time24hr(true)
                            ->seconds(false)
                            ->format('Y-m-d H:i')
                            ->label('Completed at'),
                        Textarea::make('target_audience')
                            ->label('Target audience')
                            ->columnSpanFull(),
                        Textarea::make('metadata')
                            ->label('Metadata')
                            ->columnSpanFull(),
                        TextInput::make('template_id')
                            ->label('Template')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('created_by')
                            ->label('Created by')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
