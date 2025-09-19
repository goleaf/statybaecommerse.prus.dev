<?php declare(strict_types=1);

namespace App\Filament\Resources\NewsTagResource\Pages;

use App\Filament\Resources\NewsTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsTag extends CreateRecord
{
    protected static string $resource = NewsTagResource::class;
}

