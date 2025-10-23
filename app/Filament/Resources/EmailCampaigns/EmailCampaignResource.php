<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaigns;
use App\Support\Concerns\HasNav;

use App\Filament\Resources\EmailCampaigns\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\EditEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaigns\Pages\ViewEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Models\EmailCampaign;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class EmailCampaignResource extends Resource
{
    use HasNav;

    protected static ?string $model = EmailCampaign::class;

    /**
     * @var string|BackedEnum|null Maintain compatibility with Filament v4 icon expectations.
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $form): Schema
    {
        // Delegate form configuration to the dedicated schema class for consistency across panels.
        return EmailCampaignForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Centralize table configuration to ensure column definitions remain reusable.
        return EmailCampaignsTable::configure($table);
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
            'index'  => ListEmailCampaigns::route('/'),
            'create' => CreateEmailCampaign::route('/create'),
            'view'   => ViewEmailCampaign::route('/{record}'),
            'edit'   => EditEmailCampaign::route('/{record}/edit'),
        ];
    }
}
