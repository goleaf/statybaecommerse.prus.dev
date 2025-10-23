<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\RelationManagers;


use Filament\Schemas\Schema;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class DocumentsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'documents';

    public function form(Schema $schema): Schema   
    {
        return $schema
            ->components([
                Forms\Components\Select::make('template_id')
                    ->label(__('Template'))
                    ->relationship('template', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'generated' => __('Generated'),
                        'sent'      => __('Sent'),
                    ])
                    ->required(),
                Forms\Components\Select::make('format')
                    ->label(__('Format'))
                    ->options([
                        'html' => __('HTML'),
                        'pdf'  => __('PDF'),
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table   
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('template.name')
                    ->label(__('Template'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'     => 'warning',
                        'generated' => 'success',
                        'sent'      => 'info',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('format')
                    ->label(__('Format'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'html'  => 'primary',
                        'pdf'   => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('generated_at')
                    ->label(__('Generated At'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft'     => __('Draft'),
                        'generated' => __('Generated'),
                        'sent'      => __('Sent'),
                    ]),
                Tables\Filters\SelectFilter::make('format')
                    ->label(__('Format'))
                    ->options([
                        'html' => __('HTML'),
                        'pdf'  => __('PDF'),
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                RelationManagerRepeaterAction::make()
                    ->label('Quick edit ' . $this->getPluralModelLabel())
                    ->icon('heroicon-m-pencil-square')
                    ->modalHeading('Edit ' . $this->getPluralModelLabel())
                    ->modalWidth('5xl')
                    ->configureRepeater(function (Repeater $repeater): Repeater {
                        // Provide a quick-edit modal for managing records inline.
                        return $repeater->schema($this->getQuickEditSchema());
                    }),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort('created_at', 'desc');
    }
}