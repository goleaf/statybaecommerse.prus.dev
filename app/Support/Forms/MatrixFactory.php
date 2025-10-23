<?php

declare(strict_types=1);

namespace App\Support\Forms;

use App\Forms\Components\CheckboxMatrix;

final class MatrixFactory
{
    /**
     * @param  array<string, string>  $rows
     * @param  array<string, string>  $columns
     */
    public static function checkboxGrid(string $name, array $rows, array $columns): CheckboxMatrix
    {
        return CheckboxMatrix::make($name)
            ->rows($rows)
            ->columns($columns);
    }
}
