<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DataTransferCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string}>
     */
    public static function entityFormatProvider(): array
    {
        return [
            'categories-json' => ['categories', 'json'],
            'categories-csv'  => ['categories', 'csv'],
            'products-json'   => ['products', 'json'],
            'products-csv'    => ['products', 'csv'],
            'attributes-json' => ['attributes', 'json'],
            'attributes-csv'  => ['attributes', 'csv'],
        ];
    }

    #[DataProvider('entityFormatProvider')] // Attribute form prevents deprecated docblock metadata notices in PHPUnit 11.
    public function test_data_round_trip(string $entity, string $format): void
    {
        $this->seedEntity($entity);

        $table = $this->tableFor($entity);
        $original = $this->fetchTableRows($table);

        $path = $this->temporaryPath($format);

        self::assertSame(0, Artisan::call('data:export', [
            'entity'   => $entity,
            'path'     => $path,
            '--format' => $format,
        ]));

        $this->assertFileExists($path);
        $this->mutateEntity($entity);

        self::assertSame(0, Artisan::call('data:import', [
            'entity'   => $entity,
            'path'     => $path,
            '--format' => $format,
        ]));

        $reloaded = $this->fetchTableRows($table);

        $this->assertSame(count($original), count($reloaded));
        $this->assertEquals($original, $reloaded);

        @unlink($path);
    }

    private function seedEntity(string $entity): void
    {
        match ($entity) {
            'categories' => Category::factory()->count(3)->create(),
            'products'   => Product::factory()->count(3)->create(),
            'attributes' => Attribute::factory()->count(3)->create(),
            default      => throw new InvalidArgumentException('Unsupported entity [' . $entity . '].')
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchTableRows(string $table): array
    {
        $rows = DB::table($table)
            ->orderBy('id')
            ->get()
            ->map(static function ($row): array {
                /** @var array<string, mixed> $casted */
                $casted = (array) $row;

                return $casted;
            })
            ->all();

        /** @var array<int, array<string, mixed>> $rows */
        return $rows;
    }

    private function tableFor(string $entity): string
    {
        return [
            'categories' => 'categories',
            'products'   => 'products',
            'attributes' => 'attributes',
        ][$entity] ?? throw new InvalidArgumentException('Unsupported entity [' . $entity . '].');
    }

    private function mutateEntity(string $entity): void
    {
        $table = $this->tableFor($entity);

        $rows = DB::table($table)->get(['id']);

        foreach ($rows as $row) {
            $rawId = $row->id ?? null;
            $identifier = is_scalar($rawId) || $rawId === null
                ? (string) ($rawId ?? '')
                : (string) (json_encode($rawId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

            $updates = [
                'name' => sprintf('mutated-%s-%s', $entity, $identifier),
            ];

            if (in_array($entity, ['categories', 'products', 'attributes'], true)) {
                $updates['slug'] = sprintf('mutated-%s-%s', $entity, $identifier);
            }

            DB::table($table)->where('id', $row->id)->update($updates);
        }
    }

    private function temporaryPath(string $format): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'data-transfer-');

        if ($temp === false) {
            $this->fail('Unable to create a temporary file.');
        }

        @unlink($temp);

        return $temp . '.' . $format;
    }
}
