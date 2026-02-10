<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations;

use App\Filament\Resources\Organizations\Pages\CreateOrganization;
use App\Filament\Resources\Organizations\Pages\EditOrganization;
use App\Filament\Resources\Organizations\Pages\ListOrganizations;
use App\Filament\Resources\Organizations\Schemas\OrganizationForm;
use App\Filament\Resources\Organizations\Tables\OrganizationsTable;
use App\Models\Organization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return __('admin.navigation.organization');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.organizations');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.organizations');
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UsersRelationManager::class,
            RelationManagers\ProjectsRelationManager::class,
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\FilesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOrganizations::route('/'),
            'create' => CreateOrganization::route('/create'),
            'edit'   => EditOrganization::route('/{record}/edit'),
        ];
    }
}
