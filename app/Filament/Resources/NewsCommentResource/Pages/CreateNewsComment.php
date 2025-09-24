<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsCommentResource\Pages;

use App\Filament\Resources\NewsCommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsComment extends CreateRecord
{
    protected static string $resource = NewsCommentResource::class;
}
