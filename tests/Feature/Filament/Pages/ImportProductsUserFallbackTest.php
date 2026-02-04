<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\Imports\ImportProducts;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;

uses(RefreshDatabase::class);

it('creates a fallback user for admin product imports', function () {
    $admin = AdminUser::factory()->create([
        'email' => 'admin-import@example.test',
        'name'  => 'Import Admin',
    ]);

    $page = app(ImportProducts::class);
    $reflection = new ReflectionClass($page);
    $method = $reflection->getMethod('resolveImportUser');
    $method->setAccessible(true);

    $resolved = $method->invoke($page, $admin);

    expect($resolved)->toBeInstanceOf(User::class)
        ->and($resolved->email)->toBe('admin-import@example.test');
});
