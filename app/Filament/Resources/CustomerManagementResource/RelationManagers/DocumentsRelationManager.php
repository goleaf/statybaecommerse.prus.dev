<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerManagementResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'admin.customers.documents';

    protected static ?string $modelLabel = 'admin.customers.document';

    protected static ?string $pluralModelLabel = 'admin.customers.documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('admin.documents.fields.title'))
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('template_id')
                    ->label(__('admin.documents.fields.template'))
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Textarea::make('content')
                    ->label(__('admin.documents.fields.content'))
                    ->rows(6),

                Forms\Components\Textarea::make('variables')
                    ->label(__('admin.documents.fields.variables'))
                    ->rows(3)
                    ->helperText(__('admin.variables_help')),

                Forms\Components\Select::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ])
                    ->required(),

                Forms\Components\Select::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->options([
                        'html' => __('admin.documents.formats.html'),
                        'pdf' => __('admin.documents.formats.pdf'),
                    ])
                    ->required(),

                Forms\Components\TextInput::make('file_path')
                    ->label(__('admin.documents.fields.file_path'))
                    ->maxLength(255)
                    ->placeholder(__('admin.no_file')),

                Forms\Components\Toggle::make('is_active')
                    ->label(__('admin.customers.fields.is_active'))
                    ->helperText(__('admin.is_active_help')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('admin.documents.fields.title'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('template.name')
                    ->label(__('admin.documents.fields.template'))
                    ->searchable()
                    ->placeholder(__('admin.customers.no_template')),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'gray' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.documents.status.{$state}")),

                Tables\Columns\BadgeColumn::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->colors([
                        'info' => 'html',
                        'danger' => 'pdf',
                    ])
                    ->formatStateUsing(fn (string $state): string => __("admin.documents.formats.{$state}")),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('admin.customers.fields.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('file_path')
                    ->label(__('admin.documents.fields.file_path'))
                    ->searchable()
                    ->placeholder(__('admin.not_generated')),

                Tables\Columns\TextColumn::make('generated_at')
                    ->label(__('admin.documents.fields.generated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('admin.not_generated')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ]),

                Tables\Filters\SelectFilter::make('format')
                    ->label(__('admin.documents.fields.format'))
                    ->options([
                        'html' => __('admin.documents.formats.html'),
                        'pdf' => __('admin.documents.formats.pdf'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('admin.customers.fields.is_active'))
                    ->nullable()
                    ->trueLabel(__('admin.customers.active'))
                    ->falseLabel(__('admin.customers.inactive')),

                Tables\Filters\TernaryFilter::make('has_file')
                    ->label(__('admin.customers.fields.has_file'))
                    ->nullable()
                    ->trueLabel(__('admin.customers.with_file'))
                    ->falseLabel(__('admin.customers.without_file'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('file_path'),
                        false: fn (Builder $query) => $query->whereNull('file_path'),
                        blank: fn (Builder $query) => $query,
                    ),

                Tables\Filters\Filter::make('created_at')
                    ->label(__('admin.customers.fields.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('admin.customers.filters.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('admin.customers.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('admin.documents.create')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.actions.edit')),
                Tables\Actions\Action::make('generate')
                    ->label(__('admin.documents.generate'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($record) {
                        // Document generation logic would go here
                        $record->update([
                            'generated_at' => now(),
                            'file_path' => 'generated/'.$record->id.'.pdf',
                        ]);
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label(__('admin.actions.delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('admin.actions.delete_selected')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
