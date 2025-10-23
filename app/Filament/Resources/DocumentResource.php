<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\Concerns\HasNav;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Filament\Schemas\Schema;

use Filament\Schemas\Schema;
final class DocumentResource extends Resource
{
    /** @var string|\BackedEnum|null */
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document';

    protected static ?string $model = Document::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'System';
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.documents.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.documents.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.documents.model_label');
    }

    public static function form(Form $form): Form
    {
        return $schema
            ->schema([
                Section::make(__('admin.documents.form.sections.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('admin.documents.form.fields.name'))
                                    ->required()
                                    ->maxLength(255),
                                Select::make('type')
                                    ->label(__('admin.documents.form.fields.type'))
                                    ->options([
                                        'pdf'   => 'PDF',
                                        'doc'   => 'DOC',
                                        'docx'  => 'DOCX',
                                        'xls'   => 'XLS',
                                        'xlsx'  => 'XLSX',
                                        'image' => 'Image',
                                        'other' => 'Other',
                                    ])
                                    ->required(),
                                FileUpload::make('file_path')
                                    ->label(__('admin.documents.form.fields.file_path'))
                                    ->required()
                                    ->directory('documents')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        '.pdf',
                                        '.doc',
                                        '.docx',
                                        '.xls',
                                        '.xlsx',
                                        '.jpg',
                                        '.jpeg',
                                        '.png',
                                        '.webp',
                                    ])
                                    ->maxSize(10 * 1024),
                                Textarea::make('description')
                                    ->label(__('admin.documents.form.fields.description'))
                                    ->maxLength(65535)
                                    ->nullable(),
                                Select::make('created_by')
                                    ->label(__('admin.documents.form.fields.created_by'))
                                    ->relationship('creator', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(function (): ?int {
                                        $userId = Auth::id();

                                        return $userId !== null ? (int) $userId : null;
                                    })
                                    ->nullable(),
                                Select::make('updated_by')
                                    ->label(__('admin.documents.form.fields.updated_by'))
                                    ->relationship('updater', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.documents.form.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.documents.form.fields.type'))
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('file_path')
                    ->label(__('admin.documents.form.fields.file_path'))
                    ->searchable()
                    ->limit(50),
                TextColumn::make('description')
                    ->label(__('admin.documents.form.fields.description'))
                    ->searchable()
                    ->limit(30),
                TextColumn::make('creator.name')
                    ->label(__('admin.documents.form.fields.created_by'))
                    ->badge()
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updater.name')
                    ->label(__('admin.documents.form.fields.updated_by'))
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.documents.form.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.documents.form.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.documents.form.fields.type'))
                    ->options([
                        'pdf'   => 'PDF',
                        'doc'   => 'DOC',
                        'docx'  => 'DOCX',
                        'xls'   => 'XLS',
                        'xlsx'  => 'XLSX',
                        'image' => 'Image',
                        'other' => 'Other',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'view'   => Pages\ViewDocument::route('/{record}'),
            'edit'   => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = (int) Document::count();

        return $count > 0 ? (string) $count : null;
    }
}