<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Schemas;

use App\Models\ProductRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('messages.Product'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('messages.Product'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('requested_quantity')
                            ->label(__('messages.Quantity'))
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                    ])->columns(2),

                Section::make(__('messages.Customer Information'))
                    ->schema([
                        Select::make('user_id')
                            ->label(__('messages.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('messages.Request Details'))
                    ->schema([
                        Textarea::make('message')
                            ->label(__('messages.Message'))
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make(__('messages.Status & Response'))
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
                            ->label(__('messages.Notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
