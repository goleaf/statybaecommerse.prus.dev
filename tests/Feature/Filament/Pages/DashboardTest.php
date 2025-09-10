<?php declare(strict_types=1);

use App\Models\User;
use App\Providers\Filament\AdminPanelProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('loads the admin dashboard', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    get('/admin')->assertOk();
});
