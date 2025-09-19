<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Document;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
final class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Product Documents';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('document_template_id')
                    ->label(__('admin.documents.fields.template'))
                    ->relationship('documentTemplate', 'name')
                    ->searchable(),
                    ->preload(),
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label(__('admin.documents.fields.title'))
                    ->required()\n                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('admin.documents.fields.description'))
                    ->rows(3),
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('admin.documents.fields.status'))
                    ->options([
                        'draft' => __('admin.documents.status.draft'),
                        'generated' => __('admin.documents.status.generated'),
                        'published' => __('admin.documents.status.published'),
                        'archived' => __('admin.documents.status.archived'),
                    ])
                    ->default('draft')
                Forms\Components\Select::make('format')
                    ->label(__('admin.documents.fields.format'))
                        'html' => 'HTML',
                        'pdf' => 'PDF',
                    ->default('pdf')
                Forms\Components\KeyValue::make('variables')
                    ->label(__('admin.documents.fields.variables'))
                    ->keyLabel(__('admin.documents.fields.variable_name'))
                    ->valueLabel(__('admin.documents.fields.variable_value'))
                Forms\Components\FileUpload::make('file_path')
                    ->label(__('admin.documents.fields.file'))
                    ->acceptedFileTypes(['application/pdf', 'text/html'])
                    ->directory('documents')
                    ->visibility('private')
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable(),
                Tables\Columns\TextColumn::make('documentTemplate.name')
                    ->sortable()\n                    ->badge()\n                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('format')
                        'html' => 'info',
                        'pdf' => 'danger',
                Tables\Columns\TextColumn::make('description')
                    ->limit(50),
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                Tables\Columns\TextColumn::make('generated_at')
                    ->label(__('admin.documents.fields.generated_at'))
                    ->dateTime()\n                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.documents.fields.created_at'))
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ]),
                Tables\Filters\SelectFilter::make('format')
                Tables\Filters\SelectFilter::make('document_template')
                    ->preload(),
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Tables\Actions\Action::make('generate')
                    ->label(__('admin.documents.actions.generate'))
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (Document $record) {
                        // Generate document logic here
                        $record->update([
                            'status' => 'generated',
                            'generated_at' => now(),
                        ]);
                    })
                    ->visible(fn(Document $record): bool => $record->status === 'draft'),
                Tables\Actions\Action::make('download')
                    ->label(__('admin.documents.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn(Document $record): string => $record->file_path ? route('documents.download', $record) : '#')
                    ->openUrlInNewTab()
                    ->visible(fn(Document $record): bool => $record->file_path !== null),
                DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('generate')
                        ->label(__('admin.documents.actions.generate_selected'))
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function (Document $record) {
                                $record->update([
                                    'status' => 'generated',
                                    'generated_at' => now(),
                                ]);
                            });
                        })
                        ->requiresConfirmation(),
                ]),
            ->defaultSort("created_at", "desc");
    }
}
}
