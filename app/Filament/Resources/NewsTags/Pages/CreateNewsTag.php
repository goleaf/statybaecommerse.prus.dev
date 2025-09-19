<?php

namespace App\Filament\Resources\NewsTags\Pages;

use App\Filament\Resources\NewsTags\NewsTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsTag extends CreateRecord
{
    protected static string $resource = NewsTagResource::class;
}
