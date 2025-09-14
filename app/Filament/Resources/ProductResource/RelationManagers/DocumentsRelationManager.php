<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Document;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * DocumentsRelationManager
 * 
 * Filament resource for admin panel management.
 */
class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('translations.document_name'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('title')
                    ->label(__('translations.document_title'))
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label(__('translations.document_description'))
                    ->rows(3),

                Forms\Components\FileUpload::make('file_path')
                    ->label(__('translations.document_file'))
                    ->required()
                    ->disk('public')
                    ->directory('documents')
                    ->visibility('public')
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']),

                Forms\Components\Select::make('type')
                    ->label(__('translations.document_type'))
                    ->options([
                        'manual' => __('translations.manual'),
                        'specification' => __('translations.specification'),
                        'warranty' => __('translations.warranty'),
                        'certificate' => __('translations.certificate'),
                        'other' => __('translations.other'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_public')
                    ->label(__('translations.is_public'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('translations.document_name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('translations.document_title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label(__('translations.document_type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'blue',
                        'specification' => 'green',
                        'warranty' => 'yellow',
                        'certificate' => 'purple',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('file_size')
                    ->label(__('translations.file_size'))
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 2).' KB' : '-'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('translations.is_public'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('translations.sort_order'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('translations.document_type'))
                    ->options([
                        'manual' => __('translations.manual'),
                        'specification' => __('translations.specification'),
                        'warranty' => __('translations.warranty'),
                        'certificate' => __('translations.certificate'),
                        'other' => __('translations.other'),
                    ]),

                Tables\Filters\SelectFilter::make('is_public')
                    ->label(__('translations.is_public'))
                    ->options([
                        true => __('translations.yes'),
                        false => __('translations.no'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('download')
                    ->label(__('translations.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record): string => asset('storage/'.$record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
