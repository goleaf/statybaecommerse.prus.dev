<?php declare(strict_types=1);

namespace App\Filament\Resources\EnumResource\Pages;

use App\Filament\Resources\EnumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEnum extends CreateRecord
{
    protected static string $resource = EnumResource::class;
}
