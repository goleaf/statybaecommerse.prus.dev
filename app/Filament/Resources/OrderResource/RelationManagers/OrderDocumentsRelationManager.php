<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'orders.documents';

    protected static ?string $modelLabel = 'orders.document';

    protected static ?string $pluralModelLabel = 'orders.documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('orders.document_information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('orders.document_name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('type')
                                    ->label('orders.document_type')
                                    ->options([
                                        'invoice' => 'orders.document_types.invoice',
                                        'receipt' => 'orders.document_types.receipt',
                                        'shipping_label' => 'orders.document_types.shipping_label',
                                        'return_label' => 'orders.document_types.return_label',
                                        'warranty' => 'orders.document_types.warranty',
                                        'manual' => 'orders.document_types.manual',
                                        'other' => 'orders.document_types.other',
                                    ])
                                    ->required(),
                            ]),

                        Textarea::make('description')
                            ->label('orders.description')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('file_path')
                            ->label('orders.document_file')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->directory('order-documents')
                            ->visibility('private')
                            ->required(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('file_size')
                                    ->label('orders.file_size')
                                    ->numeric()
                                    ->suffix('bytes')
                                    ->disabled(),

                                TextInput::make('mime_type')
                                    ->label('orders.mime_type')
                                    ->maxLength(255)
                                    ->disabled(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('orders.document_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('orders.document_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("orders.document_types.{$state}")),

                TextColumn::make('description')
                    ->label('orders.description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('file_size')
                    ->label('orders.file_size')
                    ->formatStateUsing(fn (?int $state): string => $state ? $this->formatFileSize($state) : '-')
                    ->sortable(),

                TextColumn::make('mime_type')
                    ->label('orders.mime_type')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('orders.created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                Action::make('download')
                    ->label('orders.download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn ($record) => route('documents.download', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
