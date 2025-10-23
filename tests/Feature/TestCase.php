<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;

abstract class TestCase extends \Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable foreign key constraints for feature tests so schema resets in
        // individual test cases (such as component-specific fixtures) can drop
        // and recreate tables without triggering SQLite constraint errors.
        Schema::disableForeignKeyConstraints();
    }

    protected function tearDown(): void
    {
        // Re-enable foreign key constraints to avoid leaking the relaxed
        // constraint behaviour into the broader test harness lifecycle.
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }
}
