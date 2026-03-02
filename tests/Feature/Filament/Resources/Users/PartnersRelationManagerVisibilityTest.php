<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\RelationManagers\PartnersRelationManager;
use App\Models\AdminUser;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PartnersRelationManagerVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_relation_manager_shows_attached_partner_even_when_disabled(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $partner = Partner::withoutGlobalScopes()->create([
            'name'       => 'Hidden Scope Partner',
            'code'       => 'HSP-' . strtoupper(substr(md5((string) microtime(true)), 0, 8)),
            'is_enabled' => false,
        ]);

        $user->partners()->attach($partner->getKey());

        Livewire::test(PartnersRelationManager::class, [
            'ownerRecord' => $user,
            'pageClass'   => EditUser::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$partner]);
    }
}
