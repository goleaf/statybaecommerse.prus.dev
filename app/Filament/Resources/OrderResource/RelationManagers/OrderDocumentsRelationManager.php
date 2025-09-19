<?php declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * OrderDocumentsRelationManager
 *
 * Comprehensive relation manager for Order Documents with advanced features:
 * - Document upload and management
 * - Document type categorization
 * - File size and type validation
 * - Document sharing and access control
 * - Bulk operations
 */
final class OrderDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'orders.documents';

    protected static ?string $modelLabel = 'orders.document';

    protected static ?string $pluralModelLabel = 'orders.documents';

    /**
     * Configure the form schema for order documents.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('orders.document_information'))
                    ->description(__('orders.document_information_description'))
                    ->icon('heroicon-o-document')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('orders.document_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-document-text'),
                                Select::make('type')
                                    ->label(__('orders.document_type'))
                                    ->options([
                                        'invoice' => __('orders.document_types.invoice'),
                                        'receipt' => __('orders.document_types.receipt'),
                                        'shipping_label' => __('orders.document_types.shipping_label'),
                                        'packing_slip' => __('orders.document_types.packing_slip'),
                                        'return_label' => __('orders.document_types.return_label'),
                                        'warranty' => __('orders.document_types.warranty'),
                                        'manual' => __('orders.document_types.manual'),
                                        'other' => __('orders.document_types.other'),
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-o-tag'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('version')
                                    ->label(__('orders.document_version'))
                                    ->maxLength(50)
                                    ->default('1.0')
                                    ->prefixIcon('heroicon-o-hashtag'),
                                Select::make('status')
                                    ->label(__('orders.document_status'))
                                    ->options([
                                        'draft' => __('orders.document_statuses.draft'),
                                        'pending' => __('orders.document_statuses.pending'),
                                        'approved' => __('orders.document_statuses.approved'),
                                        'rejected' => __('orders.document_statuses.rejected'),
                                        'archived' => __('orders.document_statuses.archived'),
                                    ])
                                    ->required()
                                    ->default('draft')
                                    ->prefixIcon('heroicon-o-flag'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.file_upload'))
                    ->description(__('orders.file_upload_description'))
                    ->icon('heroicon-o-cloud-arrow-up')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label(__('orders.document_file'))
                            ->required()
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240)  // 10MB
                            ->directory('order-documents')
                            ->visibility('private')
                            ->prefixIcon('heroicon-o-cloud-arrow-up'),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('file_size')
                                    ->label(__('orders.file_size'))
                                    ->numeric()
                                    ->suffix('KB')
                                    ->prefixIcon('heroicon-o-archive-box'),
                                TextInput::make('mime_type')
                                    ->label(__('orders.mime_type'))
                                    ->maxLength(100)
                                    ->prefixIcon('heroicon-o-document-text'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.access_control'))
                    ->description(__('orders.access_control_description'))
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_public')
                                    ->label(__('orders.is_public'))
                                    ->default(false)
                                    ->helperText(__('orders.is_public_help')),
                                Toggle::make('is_downloadable')
                                    ->label(__('orders.is_downloadable'))
                                    ->default(true)
                                    ->helperText(__('orders.is_downloadable_help')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('access_password')
                                    ->label(__('orders.access_password'))
                                    ->password()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-key'),
                                TextInput::make('expires_at')
                                    ->label(__('orders.expires_at'))
                                    ->date()
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('orders.additional_details'))
                    ->description(__('orders.additional_details_description'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('description')
                            ->label(__('orders.document_description'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('orders.document_description_help')),
                        Textarea::make('notes')
                            ->label(__('orders.document_notes'))
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(__('orders.document_notes_help')),
                    ])
                    ->collapsible(),
            ]);
    }

    /**
     * Configure the table for order documents.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('orders.document_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    })
                    ->prefixIcon('heroicon-o-document-text'),
                BadgeColumn::make('type')
                    ->label(__('orders.document_type'))
                    ->colors([
                        'primary' => 'invoice',
                        'success' => 'receipt',
                        'info' => 'shipping_label',
                        'warning' => 'packing_slip',
                        'danger' => 'return_label',
                        'secondary' => 'warranty',
                        'gray' => 'manual',
                        'slate' => 'other',
                    ])
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.document_types.{$state}") : '-'),
                BadgeColumn::make('status')
                    ->label(__('orders.document_status'))
                    ->colors([
                        'warning' => 'draft',
                        'primary' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => 'archived',
                    ])
                    ->formatStateUsing(fn(?string $state): string => $state ? __("orders.document_statuses.{$state}") : '-'),
                TextColumn::make('version')
                    ->label(__('orders.version'))
                    ->sortable()
                    ->prefixIcon('heroicon-o-hashtag'),
                TextColumn::make('file_size')
                    ->label(__('orders.file_size'))
                    ->formatStateUsing(fn(?int $state): string => $state ? number_format($state / 1024, 2) . ' MB' : '-')
                    ->sortable()
                    ->prefixIcon('heroicon-o-archive-box'),
                IconColumn::make('is_public')
                    ->label(__('orders.is_public'))
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('danger'),
                IconColumn::make('is_downloadable')
                    ->label(__('orders.is_downloadable'))
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-down-tray')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('orders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->prefixIcon('heroicon-o-calendar'),
                TextColumn::make('expires_at')
                    ->label(__('orders.expires_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->prefixIcon('heroicon-o-calendar'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('orders.document_type'))
                    ->options([
                        'invoice' => __('orders.document_types.invoice'),
                        'receipt' => __('orders.document_types.receipt'),
                        'shipping_label' => __('orders.document_types.shipping_label'),
                        'packing_slip' => __('orders.document_types.packing_slip'),
                        'return_label' => __('orders.document_types.return_label'),
                        'warranty' => __('orders.document_types.warranty'),
                        'manual' => __('orders.document_types.manual'),
                        'other' => __('orders.document_types.other'),
                    ])
                    ->multiple(),
                SelectFilter::make('status')
                    ->label(__('orders.document_status'))
                    ->options([
                        'draft' => __('orders.document_statuses.draft'),
                        'pending' => __('orders.document_statuses.pending'),
                        'approved' => __('orders.document_statuses.approved'),
                        'rejected' => __('orders.document_statuses.rejected'),
                        'archived' => __('orders.document_statuses.archived'),
                    ])
                    ->multiple(),
                TernaryFilter::make('is_public')
                    ->label(__('orders.is_public'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('is_public', true),
                        false: fn(Builder $query) => $query->where('is_public', false),
                    ),
                TernaryFilter::make('is_downloadable')
                    ->label(__('orders.is_downloadable'))
                    ->queries(
                        true: fn(Builder $query) => $query->where('is_downloadable', true),
                        false: fn(Builder $query) => $query->where('is_downloadable', false),
                    ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('orders.add_document'))
                    ->icon('heroicon-o-plus')
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
                Action::make('download')
                    ->label(__('orders.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn(Document $record): string => $record->file_path)
                    ->openUrlInNewTab()
                    ->visible(fn(Document $record): bool => $record->is_downloadable),
                Action::make('preview')
                    ->label(__('orders.preview'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn(Document $record): string => $record->file_path)
                    ->openUrlInNewTab(),
                Action::make('approve')
                    ->label(__('orders.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Document $record): bool => $record->status === 'pending')
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title(__('orders.document_approved'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('reject')
                    ->label(__('orders.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Document $record): bool => $record->status === 'pending')
                    ->action(function (Document $record): void {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title(__('orders.document_rejected'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve_documents')
                        ->label(__('orders.bulk_approve'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'approved']);

                            Notification::make()
                                ->title(__('orders.bulk_approved_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reject_documents')
                        ->label(__('orders.bulk_reject'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'rejected']);

                            Notification::make()
                                ->title(__('orders.bulk_rejected_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_public')
                        ->label(__('orders.bulk_make_public'))
                        ->icon('heroicon-o-globe-alt')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_public' => true]);

                            Notification::make()
                                ->title(__('orders.bulk_public_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_private')
                        ->label(__('orders.bulk_make_private'))
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_public' => false]);

                            Notification::make()
                                ->title(__('orders.bulk_private_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
