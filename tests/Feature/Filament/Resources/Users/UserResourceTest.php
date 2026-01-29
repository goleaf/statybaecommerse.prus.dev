<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\AdminUser;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_can_list_users_and_sort_by_columns(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $component = Livewire::test(ListUsers::class)
            ->assertSuccessful()
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('email')
            ->assertTableColumnExists('phone_number')
            ->assertTableColumnExists('is_active')
            ->assertTableColumnExists('created_at');
            
        $table = $component->instance()->getTable();
        
        $this->assertTrue($table->getColumn('name')->isSortable());
        $this->assertTrue($table->getColumn('email')->isSortable());
        $this->assertTrue($table->getColumn('phone_number')->isSortable());
        $this->assertTrue($table->getColumn('is_active')->isSortable());
        $this->assertTrue($table->getColumn('created_at')->isSortable());
    }

    public function test_admins_are_hidden_from_list(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $adminUser = User::factory()->admin()->create();

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$user])
            ->assertCanNotSeeTableRecords([$adminUser]);
    }
}
