<?php declare(strict_types=1);

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Resources\Pages\CreateRecord;

final class CreatePartner extends CreateRecord
{
    protected static string $resource = PartnerResource::class;
}

