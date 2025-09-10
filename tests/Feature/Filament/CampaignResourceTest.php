<?php declare(strict_types=1);

use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\CampaignResource\Pages\CreateCampaign as CreateCampaignPage;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns as ListCampaignsPage;
use App\Models\Campaign;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');
});

it('can render campaign resource index page', function (): void {
    actingAs($this->admin);
    Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
        ->assertOk();
});

it('can render campaign resource create page', function (): void {
    actingAs($this->admin);
    Livewire::test(CreateCampaignPage::class)
        ->assertOk();
});

it('can render campaign resource view and edit pages', function (): void {
    $campaign = Campaign::query()->create([
        'name' => 'Test Campaign',
        'slug' => 'test-campaign',
        'status' => 'draft',
    ]);

    actingAs($this->admin);
    // Avoid rendering full Filament topbar by testing the page classes directly
    Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ViewCampaign::class, ['record' => $campaign->getKey()])
        ->assertOk();
    Livewire::test(\App\Filament\Resources\CampaignResource\Pages\EditCampaign::class, ['record' => $campaign->getKey()])
        ->assertOk();
});

it('can create a campaign via filament form', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaignPage::class)
        ->fillForm([
            'status' => 'active',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'name_lt' => 'Testinė kampanija',
            'slug_lt' => 'testine-kampanija',
            'description_lt' => 'Aprašymas',
            'name_en' => 'Test Campaign',
            'slug_en' => 'test-campaign-en',
            'description_en' => 'Description',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Campaign::query()->where('slug', 'testine-kampanija')->exists())->toBeTrue();
});

it('lists campaigns and shows names', function (): void {
    $a = Campaign::factory()->create(['status' => 'active', 'name' => 'Summer Blast']);
    $b = Campaign::factory()->create(['status' => 'draft', 'name' => 'Quiet Launch']);

    actingAs($this->admin)
        ->get(CampaignResource::getUrl('index'))
        ->assertSee('Summer Blast')
        ->assertSee('Quiet Launch');
});


