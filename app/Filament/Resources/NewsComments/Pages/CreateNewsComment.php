<?php

namespace App\Filament\Resources\NewsComments\Pages;

use App\Filament\Resources\NewsComments\NewsCommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsComment extends CreateRecord
{
    protected static string $resource = NewsCommentResource::class;
}
