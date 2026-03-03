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
                    ->description(__('admin.brochures.section_brochure_description'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('messages.title'))
                            ->placeholder(__('admin.brochures.title_placeholder'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->placeholder(__('admin.brochures.description_placeholder'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('messages.active'))
                            ->helperText(__('admin.brochures.active_helper'))
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make(__('admin.brochures.section_files'))
                    ->description(__('admin.brochures.section_files_description'))
                    ->schema([
                        Repeater::make('files')
                            ->label(__('admin.brochures.files_label'))
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->placeholder(__('admin.brochures.file_name_placeholder'))
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
                                    ->helperText(__('admin.brochures.file_upload_helper'))
                                    ->openable()
                                    ->downloadable(),
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->itemLabel(static function (array $state): ?string {
                                $name = trim((string) ($state['name'] ?? ''));

                                if ($name === '') {
                                    $name = __('admin.brochures.file_row');
                                }

                                $isActive = (bool) ($state['is_active'] ?? true);
                                if (! $isActive) {
                                    return $name . ' (' . __('admin.brochures.file_inactive') . ')';
                                }

                                return $name;
                            })
                            ->addActionLabel(__('admin.brochures.add_file')),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
