<?php

declare(strict_types=1);

it('has valid PHP syntax for AutocompleteSelect component', function (): void {
    $file = app_path('Filament/Components/AutocompleteSelect.php');

    expect($file)->toBeReadableFile();

    $output = [];
    $returnCode = 0;
    exec("php -l {$file} 2>&1", $output, $returnCode);

    expect($returnCode)->toBe(0, implode("\n", $output));
});

it('has valid PHP syntax for TopNavigation component', function (): void {
    $file = app_path('Filament/Components/TopNavigation.php');

    expect($file)->toBeReadableFile();

    $output = [];
    $returnCode = 0;
    exec("php -l {$file} 2>&1", $output, $returnCode);

    expect($returnCode)->toBe(0, implode("\n", $output));
});

it('has valid PHP syntax for NavigationGroup enum', function (): void {
    $file = app_path('Enums/NavigationGroup.php');

    expect($file)->toBeReadableFile();

    $output = [];
    $returnCode = 0;
    exec("php -l {$file} 2>&1", $output, $returnCode);

    expect($returnCode)->toBe(0, implode("\n", $output));
});

it('can instantiate AutocompleteSelect component', function (): void {
    $component = new \App\Filament\Components\AutocompleteSelect('test_field');

    expect($component)->toBeInstanceOf(\App\Filament\Components\AutocompleteSelect::class);
});

it('can instantiate TopNavigation component', function (): void {
    $component = new \App\Filament\Components\TopNavigation;

    expect($component)->toBeInstanceOf(\App\Filament\Components\TopNavigation::class);
});

it('NavigationGroup enum has all required methods', function (): void {
    $reflection = new ReflectionEnum(\App\Enums\NavigationGroup::class);

    $methods = ['label', 'description', 'icon', 'color', 'priority', 'isCore', 'isAdminOnly', 'isPublic', 'requiresPermission', 'getPermission'];

    foreach ($methods as $method) {
        expect($reflection->hasMethod($method))->toBeTrue("NavigationGroup should have method: {$method}");
    }
});

it('NavigationGroup enum has static methods', function (): void {
    $reflection = new ReflectionEnum(\App\Enums\NavigationGroup::class);

    $staticMethods = ['options', 'optionsWithDescriptions', 'core', 'adminOnly', 'public', 'withPermissions', 'ordered', 'fromLabel', 'values', 'labels'];

    foreach ($staticMethods as $method) {
        expect($reflection->hasMethod($method))->toBeTrue("NavigationGroup should have static method: {$method}");
    }
});

it('NavigationGroup enum cases are accessible', function (): void {
    $cases = \App\Enums\NavigationGroup::cases();

    expect($cases)->not->toBeEmpty();
    expect($cases[0])->toBeInstanceOf(\App\Enums\NavigationGroup::class);
});

it('NavigationGroup enum can get ordered cases', function (): void {
    $ordered = \App\Enums\NavigationGroup::ordered();

    expect($ordered)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($ordered)->not->toBeEmpty();
});
