<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Tests\Support\TestingDatabase;

final class TestingDatabaseTest extends TestCase
{
    public function test_path_uses_parallel_token_when_set_and_creates_database(): void
    {
        $token = 'worker#1@AB';
        $prev = getenv('TEST_TOKEN');
        putenv('TEST_TOKEN=' . $token);

        $path = TestingDatabase::path();
        self::assertStringContainsString('testing_parallel_', basename($path));

        TestingDatabase::ensureExists();
        self::assertFileExists($path);

        // Cleanup
        @unlink($path);
        putenv('TEST_TOKEN=' . ($prev === false ? '' : $prev));
    }
}

