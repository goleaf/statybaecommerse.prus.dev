<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ReferralResource\Pages\CreateReferral;
use App\Filament\Resources\ReferralResource\Pages\EditReferral;
use App\Filament\Resources\ReferralResource\Pages\ListReferrals;
use App\Filament\Resources\ReferralResource\Pages\ViewReferral;

class ReferralResource extends \App\Filament\Resources\Referrals\ReferralResource
{
    public static function getPages(): array
    {
        return [
            'index'  => ListReferrals::route('/'),
            'create' => CreateReferral::route('/create'),
            'view'   => ViewReferral::route('/{record}'),
            'edit'   => EditReferral::route('/{record}/edit'),
        ];
    }
}
