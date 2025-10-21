<?php

declare(strict_types=1);

namespace App\Support\Forms;

use LaraZeus\MatrixChoice\Components\Matrix;

final class MatrixFactory
{
    /**
     * Build a checkbox matrix tailored for permission management.
     *
     * @param array<array-key, string> $rows
     * @param array<array-key, string> $columns
     */
    public static function permissions(array $rows, array $columns): Matrix
    {
        return self::checkboxGrid('permissions', $rows, $columns);
    }

    /**
     * Build a matrix configured for exclusive selection per row.
     *
     * @param array<array-key, string> $rows
     * @param array<array-key, string> $columns
     */
    public static function radioGrid(string $name, array $rows, array $columns, ?string $label = null): Matrix
    {
        $matrix = self::baseMatrix($name, $rows, $columns, $label);

        return $matrix->asRadio();
    }

    /**
     * Build a matrix configured for multi-select behaviour per row.
     *
     * @param array<array-key, string> $rows
     * @param array<array-key, string> $columns
     */
    public static function checkboxGrid(string $name, array $rows, array $columns, ?string $label = null): Matrix
    {
        $matrix = self::baseMatrix($name, $rows, $columns, $label);

        return $matrix->asCheckbox();
    }

    /**
     * Prime a matrix component with shared configuration concerns.
     *
     * @param array<array-key, string> $rows
     * @param array<array-key, string> $columns
     */
    private static function baseMatrix(string $name, array $rows, array $columns, ?string $label): Matrix
    {
        $matrix = Matrix::make($name)
            ->rowData($rows)
            ->columnData($columns);

        if ($label !== null) {
            $matrix->label($label);
        }

        return $matrix;
    }
}
