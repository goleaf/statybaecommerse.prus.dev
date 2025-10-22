<?php

declare(strict_types=1);

namespace App\Filament\Resources\EmailCampaigns;


use Filament\Schemas\Schema;
use App\Filament\Resources\EmailCampaigns\Pages\CreateEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\EditEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Pages\ListEmailCampaigns;
use App\Filament\Resources\EmailCampaigns\Pages\ViewEmailCampaign;
use App\Filament\Resources\EmailCampaigns\Schemas\EmailCampaignForm;
use App\Filament\Resources\EmailCampaigns\Tables\EmailCampaignsTable;
use App\Models\EmailCampaign;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema   
    {
        return EmailCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table   
    {
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
