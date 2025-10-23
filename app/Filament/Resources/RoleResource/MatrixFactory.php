<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource;

use App\Filament\Resources\RoleResource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;

final class MatrixFactory
{
    public static function permissions(): Section
    {
        $moduleSections = [];

        foreach (RoleResource::matrixDefinition() as $module => $actions) {
            $toggles = [];

            foreach (array_keys($actions) as $action) {
                $toggles[] = Toggle::make(sprintf('%s.%s', $module, $action))
                    ->label(RoleResource::abilityLabel($action))
                    ->default(false);
            }

            if ($toggles === []) {
                continue;
            }

            $moduleSections[] = Section::make(RoleResource::moduleLabel($module))
                ->schema([
                    Grid::make(max(1, min(4, count($toggles))))
                        ->schema($toggles),
                ])
                ->collapsible(false);
        }

        return Section::make(__('roles.sections.permissions'))
            ->schema($moduleSections)
            ->statePath('permissions_matrix')
            ->columns(1)
            ->collapsible();
    }
}
