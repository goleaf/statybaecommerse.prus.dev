<?php

declare(strict_types=1);

use App\Console\Commands\DataImportCommand;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('restores foreign key enforcement after truncation failure', function (): void {
    Schema::dropIfExists('fk_children');
    Schema::dropIfExists('fk_parents');

    Schema::create('fk_parents', static function (Blueprint $table): void {
        $table->id();
    });

    Schema::create('fk_children', static function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('parent_id');
        $table->foreign('parent_id')->references('id')->on('fk_parents');
    });

    try {
        $command = new DataImportCommand();

        $callTruncate = static function (string $table) use ($command): void {
            // Use reflection to invoke the protected truncateTable helper while respecting the command's final contract.
            $method = new ReflectionMethod(DataImportCommand::class, 'truncateTable');
            $method->setAccessible(true);
            $method->invoke($command, $table);
        };

        expect(foreignKeyState())->toBe(1);

        try {
            $callTruncate('non_existing_table_for_failure');
        } catch (\Throwable $exception) {
            expect($exception)->toBeInstanceOf(\Throwable::class);
        }

        expect(foreignKeyState())->toBe(1);
        expect(Schema::hasTable('fk_children'))->toBeTrue();
    } finally {
        Schema::dropIfExists('fk_children');
        Schema::dropIfExists('fk_parents');
    }
});

function foreignKeyState(): int
{
    $result = DB::selectOne('PRAGMA foreign_keys');

    return (int) ($result->foreign_keys ?? 0);
}
