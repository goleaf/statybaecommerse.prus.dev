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
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class EmailCampaignResource extends Resource
{
    use HasNav;

    protected static ?string $model = EmailCampaign::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return EmailCampaignForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
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
