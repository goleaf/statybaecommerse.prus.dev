<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Forms;

use App\Support\Forms\MatrixFactory;
use LaraZeus\MatrixChoice\Components\Matrix;
use PHPUnit\Framework\TestCase;

final class MatrixFactoryTest extends TestCase
{
    public function testPermissionsMatrixUsesCheckboxes(): void
    {
        $rows = [
            'products' => 'Products',
            'orders' => 'Orders',
        ];

        $columns = [
            'view' => 'View',
            'edit' => 'Edit',
        ];

        $matrix = MatrixFactory::permissions($rows, $columns);

        self::assertInstanceOf(Matrix::class, $matrix);
        self::assertSame('permissions', $matrix->getName());
        self::assertSame($rows, $matrix->getRowData());
        self::assertSame($columns, $matrix->getColumnData());
        self::assertSame('checkbox', $matrix->getPilColor());
    }

    public function testRadioGridConfiguration(): void
    {
        $rows = [
            'shipping' => 'Shipping',
        ];

        $columns = [
            'standard' => 'Standard',
            'express' => 'Express',
        ];

        $matrix = MatrixFactory::radioGrid('shipping_options', $rows, $columns, 'Shipping Options');

        self::assertInstanceOf(Matrix::class, $matrix);
        self::assertSame('shipping_options', $matrix->getName());
        self::assertSame('Shipping Options', $matrix->getLabel());
        self::assertSame($rows, $matrix->getRowData());
        self::assertSame($columns, $matrix->getColumnData());
        self::assertSame('radio', $matrix->getPilColor());
    }

    public function testCheckboxGridConfiguration(): void
    {
        $rows = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
        ];

        $columns = [
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
        ];

        $matrix = MatrixFactory::checkboxGrid('availability', $rows, $columns, 'Availability');

        self::assertInstanceOf(Matrix::class, $matrix);
        self::assertSame('availability', $matrix->getName());
        self::assertSame('Availability', $matrix->getLabel());
        self::assertSame($rows, $matrix->getRowData());
        self::assertSame($columns, $matrix->getColumnData());
        self::assertSame('checkbox', $matrix->getPilColor());
    }
}
