<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\CustomerGroups;

use App\Filament\Resources\CustomerGroups\Pages\CreateCustomerGroup;
use App\Filament\Resources\CustomerGroups\Pages\EditCustomerGroup;
use App\Filament\Resources\UserResource;
use App\Models\AdminUser;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

final class CustomerGroupEditPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_normalizes_translated_fields_to_plain_strings(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $group = CustomerGroup::factory()->create([
            'name'        => null,
            'description' => null,
        ]);

        DB::table('customer_groups')
            ->where('id', $group->getKey())
            ->update([
                'name'        => json_encode(['lt' => 'Retail Customers', 'en' => 'Retail Customers']),
                'description' => json_encode(['lt' => 'Default customer group', 'en' => 'Default customer group']),
            ]);

        Livewire::test(EditCustomerGroup::class, [
            'record' => $group->getRouteKey(),
        ])
            ->assertSuccessful()
            ->assertSet('data.name', 'Retail Customers')
            ->assertSet('data.description', 'Default customer group');
    }

    public function test_create_page_attaches_created_group_to_user_when_attach_query_is_present(): void
    {
        $this->resolveAdminPanel();
        $this->actingAs(AdminUser::factory()->create(), 'admin');

        $user = User::factory()->create();
        $redirectUrl = UserResource::getUrl('edit', ['record' => $user]) . '?relation=0';

        Livewire::withQueryParams([
            'attach_user_id' => $user->getKey(),
            'redirect'       => $redirectUrl,
        ])
            ->test(CreateCustomerGroup::class)
            ->fillForm([
                'name' => 'Attached group',
                'type' => 'retail',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect($redirectUrl);

        $group = CustomerGroup::query()
            ->latest('id')
            ->first();

        $this->assertNotNull($group);

        $this->assertDatabaseHas('customer_group_user', [
            'user_id'           => $user->getKey(),
            'customer_group_id' => $group->getKey(),
        ]);
    }
}
