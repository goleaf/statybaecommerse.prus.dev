<?php declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateLegal extends CreateRecord
{
    protected static string $resource = LegalResource::class;
}
