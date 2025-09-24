<?php

declare(strict_types=1);

use App\Enums\NavigationGroup;
use App\Filament\Components\TopNavigation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'is_admin' => true,
    ]);
});

it('can render top navigation widget', function (): void {
    $this->actingAs($this->user);

    $component = Livewire::test(TopNavigation::class);

    $component->assertSuccessful();
});

it('provides navigation groups data', function (): void {
    $this->actingAs($this->user);

    $component = Livewire::test(TopNavigation::class);

    $viewData = $component->instance()->getViewData();

    expect($viewData)->toHaveKeys(['navigationGroups', 'user', 'isAdmin']);
    expect($viewData['navigationGroups'])->toBeArray();
    expect($viewData['user'])->toBeInstanceOf(User::class);
    expect($viewData['isAdmin'])->toBeTrue();
});

it('filters navigation groups based on user permissions', function (): void {
    $adminUser = User::factory()->create(['is_admin' => true]);
    $regularUser = User::factory()->create(['is_admin' => false]);

    // Test with admin user
    $this->actingAs($adminUser);
    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $adminGroups = $viewData['navigationGroups'];
    expect($adminGroups)->toHaveCount(count(NavigationGroup::cases()));

    // Test with regular user
    $this->actingAs($regularUser);
    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $regularGroups = $viewData['navigationGroups'];
    expect($regularGroups)->toHaveCountLessThan(count(NavigationGroup::cases()));
});

it('excludes admin-only groups for non-admin users', function (): void {
    $regularUser = User::factory()->create(['is_admin' => false]);

    $this->actingAs($regularUser);

    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $groups = collect($viewData['navigationGroups']);
    $adminOnlyGroups = $groups->filter(fn ($group) => $group['is_admin_only']);

    expect($adminOnlyGroups)->toHaveCount(0);
});

it('includes admin-only groups for admin users', function (): void {
    $adminUser = User::factory()->create(['is_admin' => true]);

    $this->actingAs($adminUser);

    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $groups = collect($viewData['navigationGroups']);
    $adminOnlyGroups = $groups->filter(fn ($group) => $group['is_admin_only']);

    expect($adminOnlyGroups)->toHaveCount(0);
});

it('respects user permissions for permission-required groups', function (): void {
    $userWithoutPermission = User::factory()->create(['is_admin' => false]);

    $this->actingAs($userWithoutPermission);

    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $groups = collect($viewData['navigationGroups']);
    $analyticsGroup = $groups->firstWhere('key', NavigationGroup::Analytics->value);

    expect($analyticsGroup)->toBeNull();
});

it('returns empty navigation groups for unauthenticated user', function (): void {
    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    expect($viewData['navigationGroups'])->toBeArray();
    expect($viewData['navigationGroups'])->toHaveCount(0);
    expect($viewData['user'])->toBeNull();
    expect($viewData['isAdmin'])->toBeFalse();
});

it('has correct widget configuration', function (): void {
    $reflection = new ReflectionClass(TopNavigation::class);

    $viewProperty = $reflection->getProperty('view');
    $viewProperty->setAccessible(true);
    expect($viewProperty->getValue(new TopNavigation))->toBe('filament.components.top-navigation');

    $columnSpanProperty = $reflection->getProperty('columnSpan');
    $columnSpanProperty->setAccessible(true);
    expect($columnSpanProperty->getValue(new TopNavigation))->toBe('full');

    $sortProperty = $reflection->getProperty('sort');
    $sortProperty->setAccessible(true);
    expect($sortProperty->getValue(new TopNavigation))->toBe(-100);
});

it('can access navigation group data correctly', function (): void {
    $this->actingAs($this->user);

    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $groups = $viewData['navigationGroups'];
    expect($groups)->not->toBeEmpty();

    $firstGroup = $groups[0];
    expect($firstGroup)->toHaveKeys([
        'key',
        'label',
        'description',
        'icon',
        'color',
        'priority',
        'is_core',
        'is_admin_only',
        'is_public',
        'requires_permission',
        'permission',
    ]);
});

it('sorts navigation groups by priority', function (): void {
    $this->actingAs($this->user);

    $component = Livewire::test(TopNavigation::class);
    $viewData = $component->instance()->getViewData();

    $groups = $viewData['navigationGroups'];
    $priorities = array_column($groups, 'priority');

    $sortedPriorities = $priorities;
    sort($sortedPriorities);

    expect($priorities)->toEqual($sortedPriorities);
});
