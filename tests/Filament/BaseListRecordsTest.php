<?php

declare(strict_types=1);

use App\Filament\Pages\Support\BaseListRecords;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

uses(TestCase::class);
uses()->group('filament');

it('boots and touches the table records when loadTable runs without prior Livewire lifecycle', function (): void {
    // Instantiate the harness and emulate an uninitialized table state before invoking the helper.
    $page = new BaseListRecordsHarness();
    $page->resetTableState();

    $page->loadTable();

    expect($page->bootedCounter)->toBe(1)
        ->and($page->recordsCounter)->toBe(1)
        ->and($page->tableBootstrapped)->toBeTrue();
});

it('mounts the create action when no action is already active', function (): void {
    // Execute the convenience shim while the mounted action stack is empty to confirm it mounts the create action.
    $page = new BaseListRecordsHarness();
    $page->mountedActions = [];

    $page->create();

    expect($page->mountActionCalls)->toBe(['create'])
        ->and($page->lastMountedAction)->toBeNull();
});

it('executes the mounted create action when the modal is already open', function (): void {
    // Preload the mounted actions so the shim reuses the existing modal instead of double-mounting the action.
    $page = new BaseListRecordsHarness();
    $page->mountedActions = [
        ['name' => 'edit'],
        ['name' => 'create'],
    ];

    $page->create();

    expect($page->lastMountedAction)->toBe('create')
        ->and($page->mountActionCalls)->toBe([]);
});

/**
 * Harness page that surfaces the BaseListRecords helpers for targeted assertions.
 */
final class BaseListRecordsHarness extends BaseListRecords
{
    protected static string $resource = BaseListRecordsHarnessResource::class;

    public int $bootedCounter = 0;

    public int $recordsCounter = 0;

    public bool $tableBootstrapped = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $mountedActions = [];

    /**
     * @var array<int, string>
     */
    public array $mountActionCalls = [];

    public ?string $lastMountedAction = null;

    public function resetTableState(): void
    {
        // Null the table property so the shim exercises the boot cycle explicitly.
        $this->table = null;
    }

    protected function bootedInteractsWithTable(): void
    {
        // Track how many times the Livewire hook executes and mark the table as bootstrapped.
        $this->bootedCounter++;
        $this->tableBootstrapped = true;
    }

    public function getTableRecords(): iterable
    {
        // Increment the counter to prove the shim touches the records collection before returning.
        $this->recordsCounter++;

        return [];
    }

    public function mountAction(string $name, array $arguments = []): mixed
    {
        // Record the mounted action name so tests can assert the shim behaviour.
        $this->mountActionCalls[] = $name;

        return null;
    }

    public function callMountedAction(?string $name = null): mixed
    {
        // Persist the invoked action name so the tests can verify the shortcut path.
        $this->lastMountedAction = $name ?? 'create';

        return null;
    }
}

final class BaseListRecordsHarnessModel extends Model
{
    // Intentionally empty – the harness never touches persistence.
}

final class BaseListRecordsHarnessResource extends Resource
{
    protected static ?string $model = BaseListRecordsHarnessModel::class;

    public static function getPages(): array
    {
        // No actual routes are required for the harness.
        return [];
    }

    public static function canViewAny(?Model $user = null): bool
    {
        // Allow access unconditionally so the authorization shim never aborts.
        return true;
    }
}
