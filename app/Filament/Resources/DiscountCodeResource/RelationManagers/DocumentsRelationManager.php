<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountCodeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('template_id')
                    ->label(__('Template'))
                    ->relationship('template', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                
                Forms\Components\TextInput::make('title')
                    ->label(__('Title'))
                    ->required(),
                    ->maxLength(255),
                
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'generated' => __('Generated'),
                        'sent' => __('Sent'),
                    ])
                    ->required(),
                
                Forms\Components\Select::make('format')
                    ->label(__('Format'))
                    ->options([
                        'html' => __('HTML'),
                        'pdf' => __('PDF'),
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('Title'))
                    ->searchable(),
                    ->sortable(),
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('template.name')
                    ->label(__('Template'))
                    ->searchable(),
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'warning',
                        'generated' => 'success',
                        'sent' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('format')
                    ->label(__('Format'))
                    ->badge(),
                    ->color(fn (string $state): string => match ($state) {
                        'html' => 'primary',
                        'pdf' => 'danger',
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
                        'draft' => __('Draft'),
                        'generated' => __('Generated'),
                        'sent' => __('Sent'),
                    ]),
                
                Tables\Filters\SelectFilter::make('format')
                    ->label(__('Format'))
                    ->options([
                        'html' => __('HTML'),
                        'pdf' => __('PDF'),
                    ]),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->defaultSort("created_at", "desc");
    }
}
    }
}

