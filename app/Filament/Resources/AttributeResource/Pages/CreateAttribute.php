<?php declare(strict_types=1);

namespace App\Filament\Resources\AttributeResource\Pages;

use App\Filament\Resources\AttributeResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateAttribute extends CreateRecord
{
    protected static string $resource = AttributeResource::class;

}
