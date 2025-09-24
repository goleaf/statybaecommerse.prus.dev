<?php

namespace App\Filament\Resources\EmailCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmailCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('html_content')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                Textarea::make('target_audience')
                    ->columnSpanFull(),
                TextInput::make('total_recipients')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sent_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('delivered_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('opened_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('clicked_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('unsubscribed_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('scheduled_at'),
                DateTimePicker::make('sent_at'),
                DateTimePicker::make('completed_at'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
