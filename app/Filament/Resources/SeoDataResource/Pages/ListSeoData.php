<?php declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use Filament\Resources\Pages\ListRecords;

final class ListSeoData extends ListRecords
{
    protected static string $resource = SeoDataResource::class;
}
