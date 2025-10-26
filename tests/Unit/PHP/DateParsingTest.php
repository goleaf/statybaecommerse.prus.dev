<?php

declare(strict_types=1);

namespace Tests\Unit\PHP;

use App\Support\DateParser;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class DateParsingTest extends TestCase
{
    public function test_parse_returns_null_for_invalid_inputs(): void
    {
        Carbon::setTestNow('2024-01-01 12:00:00');

        self::assertNull(DateParser::parse(null));
        self::assertNull(DateParser::parse(''));
        self::assertNull(DateParser::parse('   '));
        self::assertNull(DateParser::parse('not-a-date'));
        self::assertNull(DateParser::parse(123));

        Carbon::setTestNow();
    }

    public function test_parse_default_today_when_invalid(): void
    {
        Carbon::setTestNow('2024-01-01 08:00:00');

        $parsed = DateParser::parse('invalid date', 'today');
        self::assertInstanceOf(Carbon::class, $parsed);
        self::assertSame('2024-01-01', $parsed->toDateString());

        Carbon::setTestNow();
    }

    public function test_from_format_valid_and_invalid(): void
    {
        $valid = DateParser::fromFormat('Y-m-d', '2024-02-10');
        self::assertInstanceOf(Carbon::class, $valid);
        self::assertSame('2024-02-10', $valid->toDateString());

        $invalid = DateParser::fromFormat('Y-m-d', '10/02/2024');
        self::assertNull($invalid);

        $invalid2 = DateParser::fromFormat('Y-m-d', '2024-13-40');
        self::assertNull($invalid2);
    }
}
