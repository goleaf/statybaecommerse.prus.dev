<?php

declare(strict_types=1);

use App\Filament\Concerns\HasResizableColumns;
use Mockery;
use Tests\TestCase;

uses(TestCase::class);
uses()->group('filament');

afterEach(function (): void {
    // Reset Mockery expectations between tests to avoid leaking spies across cases.
    Mockery::close();
});

it('skips persisting column widths when no admin guard is authenticated', function (): void {
    // Create a partial mock so we can assert the vendor persistence hook is never triggered for guests.
    $harness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $harness->fakeUserId = null;

    $harness->shouldReceive('vendorPersistColumnWidthsToDatabase')->never();

    $harness->traitPersistColumnWidthsToDatabase();
});

it('persists column widths once the admin guard is available', function (): void {
    // Attach a fake admin identifier to confirm the vendor persistence hook runs exactly once.
    $harness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $harness->fakeUserId = 42;

    $harness->shouldReceive('vendorPersistColumnWidthsToDatabase')->once();

    $harness->traitPersistColumnWidthsToDatabase();
});

it('clears cached widths instead of hitting the database for guests', function (): void {
    // Seed the cached widths to verify they reset when the admin guard is absent.
    $harness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $harness->fakeUserId = null;
    $harness->columnWidths = ['orders.id' => 320];

    $harness->shouldReceive('vendorLoadColumnWidthsFromDatabase')->never();

    $harness->traitLoadColumnWidthsFromDatabase();

    expect($harness->columnWidths)->toBe([]);
});

it('loads persisted widths when the admin guard is populated', function (): void {
    // Provide an admin identifier so the database-backed loader is invoked as expected.
    $harness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $harness->fakeUserId = 99;

    $harness->shouldReceive('vendorLoadColumnWidthsFromDatabase')->once();

    $harness->traitLoadColumnWidthsFromDatabase();
});

it('scopes the session cache key by panel identifier and admin id', function (): void {
    // Prime the vendor key response and verify the guard-aware suffixes are appended correctly.
    $guestHarness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $guestHarness->fakeUserId = null;

    $guestHarness->shouldReceive('vendorGetSessionKey')
        ->once()
        ->andReturn('filament.resizable_columns');

    expect($guestHarness->traitGetSessionKey())->toBe('filament.resizable_columns');

    $adminHarness = Mockery::mock(HasResizableColumnsHarness::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $adminHarness->fakeUserId = 77;

    $adminHarness->shouldReceive('vendorGetSessionKey')
        ->once()
        ->andReturn('filament.resizable_columns');

    expect($adminHarness->traitGetSessionKey())
        ->toBe('filament.resizable_columns_admin_77');
});

/**
 * Lightweight harness exposing the protected helpers from the resizable column concern for assertions.
 */
final class HasResizableColumnsHarness
{
    use HasResizableColumns {
        persistColumnWidthsToDatabase as public traitPersistColumnWidthsToDatabase;
        loadColumnWidthsFromDatabase as public traitLoadColumnWidthsFromDatabase;
        getSessionKey as public traitGetSessionKey;
        basePersistColumnWidthsToDatabase as protected vendorPersistColumnWidthsToDatabase;
        baseLoadColumnWidthsFromDatabase as protected vendorLoadColumnWidthsFromDatabase;
        baseGetSessionKey as protected vendorGetSessionKey;
    }

    /**
     * @var array<string, int>
     */
    public array $columnWidths = ['orders.id' => 240];

    public ?int $fakeUserId = null;

    protected function getUserId(): int|string|null
    {
        // Provide the configurable guard identifier so the tests can emulate guest/admin scenarios.
        return $this->fakeUserId;
    }
}
