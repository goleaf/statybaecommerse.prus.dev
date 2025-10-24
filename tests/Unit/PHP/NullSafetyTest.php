<?php

declare(strict_types=1);

namespace Tests\Unit\PHP;

use ArrayObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NullSafetyTest extends TestCase
{
    #[DataProvider('countSafetyProvider')]
    public function test_count_safety(mixed $value, int $expected): void
    {
        $actual = is_countable($value) ? count($value) : 0;
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{0:mixed,1:int}>
     */
    public static function countSafetyProvider(): array
    {
        return [
            'null' => [null, 0],
            'int' => [123, 0],
            'string' => ['abc', 0],
            'empty array' => [[], 0],
            'array' => [[1, 2, 3], 3],
            'ArrayObject' => [new ArrayObject([1, 2]), 2],
            'object' => [new \stdClass(), 0],
        ];
    }

    #[DataProvider('strposProvider')]
    public function test_strpos_haystack_cast(mixed $haystack, string $needle, int|false $expected): void
    {
        $actual = strpos((string) $haystack, $needle);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{0:mixed,1:string,2:int|false}>
     */
    public static function strposProvider(): array
    {
        return [
            'string match at start' => ['abc', 'a', 0],
            'string match in middle' => ['abc', 'b', 1],
            'int haystack' => [12345, '23', 1],
            'null haystack' => [null, 'a', false],
        ];
    }

    #[DataProvider('explodeProvider')]
    public function test_explode_string_cast(string $delimiter, mixed $value, array $expected): void
    {
        $actual = explode($delimiter, (string) $value);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{0:string,1:mixed,2:array}>
     */
    public static function explodeProvider(): array
    {
        return [
            'normal string' => [',', 'a,b', ['a', 'b']],
            'int value' => ['-', 12345, ['12345']],
            'null value' => [',', null, ['']],
        ];
    }

    #[DataProvider('jsonDecodeArrayProvider')]
    public function test_safe_json_decode_array(mixed $value, array $expected): void
    {
        $actual = safe_json_decode_array($value);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{0:mixed,1:array}>
     */
    public static function jsonDecodeArrayProvider(): array
    {
        return [
            'valid object' => ['{"a":1}', ['a' => 1]],
            'valid array' => ['[]', []],
            'empty string' => ['', []],
            'null' => [null, []],
            'already array' => [['a' => 1], ['a' => 1]],
            'invalid json' => ['{not valid}', []],
        ];
    }
}
