<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentTemplateResource\Pages\CreateDocumentTemplate;
use App\Filament\Resources\DocumentTemplateResource\Pages\EditDocumentTemplate;
use App\Filament\Resources\DocumentTemplateResource\Pages\ListDocumentTemplates;
use App\Filament\Resources\DocumentTemplateResource\Pages\ViewDocumentTemplate;
use App\Filament\Resources\DocumentTemplateResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\DocumentTemplateResource\Schemas\DocumentTemplateForm;
use App\Filament\Resources\DocumentTemplateResource\Schemas\DocumentTemplateInfolist;
use App\Filament\Resources\DocumentTemplateResource\Tables\DocumentTemplatesTable;
use App\Models\DocumentTemplate;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

final class DocumentTemplateResource extends BaseResource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('messages.documents');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.document_templates.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.document_templates.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.document_templates.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentTemplateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDocumentTemplates::route('/'),
            'create' => CreateDocumentTemplate::route('/create'),
            'view'   => ViewDocumentTemplate::route('/{record}'),
            'edit'   => EditDocumentTemplate::route('/{record}/edit'),
        ];
    }
}
