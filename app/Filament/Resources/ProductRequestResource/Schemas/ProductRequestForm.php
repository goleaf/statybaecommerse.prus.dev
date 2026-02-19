<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Schemas;

use App\Models\ProductRequest;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.product'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('messages.product'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('requested_quantity')
                            ->label(__('messages.quantity'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])->columns(2),

                Section::make(__('messages.customer_information'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('messages.user'))
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! filled($state)) {
                                    return;
                                }

                                $user = User::query()->find((int) $state);
                                if ($user === null) {
                                    return;
                                }

                                $set('name', (string) $user->name);
                                $set('email', (string) $user->email);
                                $set('phone', (string) ($user->phone ?? $user->phone_number ?? ''));
                            }),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->required()
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('messages.request_details'))
                    ->schema([
                        Textarea::make('message')
                            ->label(__('messages.message'))
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('messages.status_response'))
                    ->schema([
                        Select::make('status')
                            ->label(__('messages.status'))
                            ->options([
                                ProductRequest::STATUS_PENDING     => __('translations.status_pending'),
                                ProductRequest::STATUS_IN_PROGRESS => __('translations.status_in_progress'),
                                ProductRequest::STATUS_COMPLETED   => __('translations.status_completed'),
                                ProductRequest::STATUS_CANCELLED   => __('translations.status_cancelled'),
                            ])
                            ->required()
                            ->default(ProductRequest::STATUS_PENDING)
                            ->selectablePlaceholder(false)
                            ->native(false),
                        Textarea::make('admin_notes')
                            ->label(__('messages.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
