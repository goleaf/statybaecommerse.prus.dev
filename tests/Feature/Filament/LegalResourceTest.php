<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\LegalResource;
use App\Models\Legal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LegalResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_can_render_legal_index_page(): void
    {
        $this
            ->get(LegalResource::getUrl('index'))
            ->assertOk();
    }

    public function test_can_list_legal_pages(): void
    {
        $legalPages = Legal::factory()->count(3)->create();

        Livewire::test(LegalResource\Pages\ListLegals::class)
            ->assertCanSeeTableRecords($legalPages);
    }

    public function test_can_render_legal_create_page(): void
    {
        $this
            ->get(LegalResource::getUrl('create'))
            ->assertOk();
    }

    public function test_can_create_legal_page(): void
    {
        $newData = Legal::factory()->make();

        Livewire::test(LegalResource\Pages\CreateLegal::class)
            ->fillForm([
                'key' => $newData->key,
                'is_enabled' => $newData->is_enabled,
                'translations' => [
                    [
                        'locale' => 'en',
                        'title' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                        'content' => 'This is our privacy policy content.',
                    ],
                    [
                        'locale' => 'lt',
                        'title' => 'Privatumo politika',
                        'slug' => 'privatumo-politika',
                        'content' => 'Čia yra mūsų privatumo politikos turinys.',
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('legals', [
            'key' => $newData->key,
            'is_enabled' => $newData->is_enabled,
        ]);
    }

    public function test_can_render_legal_edit_page(): void
    {
        $legal = Legal::factory()->create();

        $this
            ->get(LegalResource::getUrl('edit', ['record' => $legal]))
            ->assertOk();
    }

    public function test_can_edit_legal_page(): void
    {
        $legal = Legal::factory()->create();
        $newData = Legal::factory()->make();

        Livewire::test(LegalResource\Pages\EditLegal::class, ['record' => $legal->getRouteKey()])
            ->fillForm([
                'key' => $newData->key,
                'is_enabled' => $newData->is_enabled,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($legal->refresh())
            ->key
            ->toBe($newData->key)
            ->is_enabled
            ->toBe($newData->is_enabled);
    }

    public function test_can_delete_legal_page(): void
    {
        $legal = Legal::factory()->create();

        Livewire::test(LegalResource\Pages\EditLegal::class, ['record' => $legal->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($legal);
    }

    public function test_can_validate_legal_key_is_required(): void
    {
        Livewire::test(LegalResource\Pages\CreateLegal::class)
            ->fillForm([
                'key' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'required']);
    }

    public function test_can_validate_legal_key_is_unique(): void
    {
        $legal = Legal::factory()->create();

        Livewire::test(LegalResource\Pages\CreateLegal::class)
            ->fillForm([
                'key' => $legal->key,
            ])
            ->call('create')
            ->assertHasFormErrors(['key' => 'unique']);
    }

    public function test_can_filter_legal_pages_by_enabled_status(): void
    {
        $enabledLegal = Legal::factory()->create(['is_enabled' => true]);
        $disabledLegal = Legal::factory()->create(['is_enabled' => false]);

        Livewire::test(LegalResource\Pages\ListLegals::class)
            ->filterTable('is_enabled', true)
            ->assertCanSeeTableRecords([$enabledLegal])
            ->assertCanNotSeeTableRecords([$disabledLegal]);
    }

    public function test_can_search_legal_pages_by_key(): void
    {
        $legalA = Legal::factory()->create(['key' => 'privacy-policy']);
        $legalB = Legal::factory()->create(['key' => 'terms-of-service']);

        Livewire::test(LegalResource\Pages\ListLegals::class)
            ->searchTable('privacy')
            ->assertCanSeeTableRecords([$legalA])
            ->assertCanNotSeeTableRecords([$legalB]);
    }
}
