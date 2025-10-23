<?php

declare(strict_types=1);

namespace App\Support\Forms;

use LaraZeus\MatrixChoice\Components\Matrix;

final class MatrixFactory
{
    private function __construct()
    {
        // Prevent instantiation.
    }

    /**
     * Build a standardized permissions matrix using checkbox selections.
     *
     * @param array<string, string> $rows
     * @param array<string, string> $columns
     */
    public static function permissions(array $rows, array $columns): Matrix
    {
        return self::checkboxGrid('permissions_matrix', $rows, $columns)
            ->rowSelectRequired(false);
    }

    /**
     * Build a radio-based matrix field for mutually exclusive selections.
     *
     * @param array<string, string> $rows
     * @param array<string, string> $columns
     */
    public static function radioGrid(string $name, array $rows, array $columns, ?string $label = null): Matrix
    {
        $matrix = Matrix::make($name)
            ->asRadio()
            ->columnData($columns)
            ->rowData($rows);

        if ($label !== null) {
            $matrix->label($label);
        }

        return $matrix;
    }

    /**
     * Build a checkbox-based matrix field for multi-select grids.
     *
     * @param array<string, string> $rows
     * @param array<string, string> $columns
     */
    public static function checkboxGrid(string $name, array $rows, array $columns, ?string $label = null): Matrix
    {
        $matrix = Matrix::make($name)
            ->asCheckbox()
            ->columnData($columns)
            ->rowData($rows)
            ->rowSelectRequired(false);

        if ($label !== null) {
            $matrix->label($label);
        }

        return $matrix;
    }
}
