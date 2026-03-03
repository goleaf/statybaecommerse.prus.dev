<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Schemas;

use App\Support\Storage\SecureStorage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class BrochureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.brochures.section_brochure'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('messages.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label(__('messages.sort_order'))
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('admin.brochures.section_files'))
                    ->schema([
                        Repeater::make('files')
                            ->label(__('admin.brochures.files_label'))
                            ->relationship()
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required()
                                    ->maxLength(255),
                                FileUpload::make('file_path')
                                    ->label(__('admin.brochures.file_label'))
                                    ->required()
                                    ->disk(SecureStorage::disk())
                                    ->directory('brochures')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', '.pdf'])
                                    ->maxSize(50 * 1024)
                                    ->openable()
                                    ->downloadable(),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric()
                                    ->integer()
                                    ->default(0)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->itemLabel(static function (array $state): ?string {
                                $name = trim((string) ($state['name'] ?? ''));

                                return $name !== '' ? $name : __('admin.brochures.file_row');
                            })
                            ->addActionLabel(__('admin.brochures.add_file')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
