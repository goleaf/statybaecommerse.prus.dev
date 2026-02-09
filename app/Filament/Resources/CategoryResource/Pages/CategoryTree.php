<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use SolutionForest\FilamentTree\Resources\Pages\TreePage;

class CategoryTree extends TreePage
{
    protected static string $resource = CategoryResource::class;
}
