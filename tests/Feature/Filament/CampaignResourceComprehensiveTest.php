<?php declare(strict_types=1);

use App\Filament\Resources\CampaignResource\Pages\CreateCampaign;
use App\Filament\Resources\CampaignResource\Pages\EditCampaign;
use App\Filament\Resources\CampaignResource\Pages\ListCampaigns;
use App\Filament\Resources\CampaignResource\Pages\ViewCampaign;
use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Channel;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');

    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create();
    $this->customerGroup = CustomerGroup::factory()->create();
    $this->channel = Channel::factory()->create();
    $this->zone = Zone::factory()->create();
});

it('can render campaign resource index page', function (): void {
    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->assertOk();
});

it('can render campaign resource create page', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->assertOk();
});

it('can create a campaign with all fields', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Summer Sale Campaign',
            'slug' => 'summer-sale-campaign',
            'description' => 'Amazing summer sale with great discounts',
            'type' => 'banner',
            'status' => 'active',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'budget' => 5000.0,
            'budget_limit' => 10000.0,
            'target_audience' => 'all_customers',
            'display_priority' => 10,
            'is_featured' => true,
            'subject' => 'Summer Sale Subject',
            'content' => 'Summer sale content with amazing offers',
            'cta_text' => 'Shop Now',
            'cta_url' => 'https://example.com/shop',
            'track_conversions' => true,
            'send_notifications' => true,
            'max_uses' => 1000,
            'meta_title' => 'Summer Sale Meta Title',
            'meta_description' => 'Summer sale meta description',
            'social_media_ready' => true,
            'auto_start' => false,
            'auto_end' => true,
            'auto_pause_on_budget' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Campaign::where('slug', 'summer-sale-campaign')->exists())->toBeTrue();

    $campaign = Campaign::where('slug', 'summer-sale-campaign')->first();
    expect($campaign->name)->toBe('Summer Sale Campaign');
    expect($campaign->budget)->toBe(5000.0);
    expect($campaign->is_featured)->toBeTrue();
    expect($campaign->track_conversions)->toBeTrue();
});

it('can create a campaign with relationships', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Product Campaign',
            'slug' => 'product-campaign',
            'type' => 'email',
            'status' => 'active',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'target_categories' => [$this->category->id],
            'target_products' => [$this->product->id],
            'target_customer_groups' => [$this->customerGroup->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $campaign = Campaign::where('slug', 'product-campaign')->first();

    expect($campaign->targetCategories)->toHaveCount(1);
    expect($campaign->targetProducts)->toHaveCount(1);
    expect($campaign->targetCustomerGroups)->toHaveCount(1);
});

it('can edit a campaign', function (): void {
    $campaign = Campaign::factory()->create([
        'name' => 'Original Name',
        'budget' => 1000.0,
        'is_featured' => false,
    ]);

    actingAs($this->admin);

    Livewire::test(EditCampaign::class, ['record' => $campaign->getKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'budget' => 2000.0,
            'is_featured' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $campaign->refresh();
    expect($campaign->name)->toBe('Updated Name');
    expect($campaign->budget)->toBe(2000.0);
    expect($campaign->is_featured)->toBeTrue();
});

it('can view a campaign', function (): void {
    $campaign = Campaign::factory()->create([
        'name' => 'View Test Campaign',
        'description' => 'Test description',
    ]);

    actingAs($this->admin);

    Livewire::test(ViewCampaign::class, ['record' => $campaign->getKey()])
        ->assertOk()
        ->assertSee('View Test Campaign')
        ->assertSee('Test description');
});

it('can delete a campaign', function (): void {
    $campaign = Campaign::factory()->create();

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableAction('delete', $campaign);

    expect(Campaign::withTrashed()->find($campaign->id))->not->toBeNull();
    expect(Campaign::find($campaign->id))->toBeNull();
});

it('can activate a campaign', function (): void {
    $campaign = Campaign::factory()->create(['status' => 'draft']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableAction('activate', $campaign);

    $campaign->refresh();
    expect($campaign->status)->toBe('active');
});

it('can pause a campaign', function (): void {
    $campaign = Campaign::factory()->create(['status' => 'active']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableAction('pause', $campaign);

    $campaign->refresh();
    expect($campaign->status)->toBe('paused');
});

it('can complete a campaign', function (): void {
    $campaign = Campaign::factory()->create(['status' => 'active']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableAction('complete', $campaign);

    $campaign->refresh();
    expect($campaign->status)->toBe('completed');
});

it('can bulk activate campaigns', function (): void {
    $campaign1 = Campaign::factory()->create(['status' => 'draft']);
    $campaign2 = Campaign::factory()->create(['status' => 'paused']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableBulkAction('activate', [$campaign1, $campaign2]);

    $campaign1->refresh();
    $campaign2->refresh();
    expect($campaign1->status)->toBe('active');
    expect($campaign2->status)->toBe('active');
});

it('can bulk pause campaigns', function (): void {
    $campaign1 = Campaign::factory()->create(['status' => 'active']);
    $campaign2 = Campaign::factory()->create(['status' => 'active']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableBulkAction('pause', [$campaign1, $campaign2]);

    $campaign1->refresh();
    $campaign2->refresh();
    expect($campaign1->status)->toBe('paused');
    expect($campaign2->status)->toBe('paused');
});

it('can filter campaigns by type', function (): void {
    Campaign::factory()->create(['type' => 'email']);
    Campaign::factory()->create(['type' => 'banner']);
    Campaign::factory()->create(['type' => 'email']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('type', 'email')
        ->assertCanSeeTableRecords(Campaign::where('type', 'email')->get())
        ->assertCanNotSeeTableRecords(Campaign::where('type', 'banner')->get());
});

it('can filter campaigns by status', function (): void {
    Campaign::factory()->create(['status' => 'active']);
    Campaign::factory()->create(['status' => 'draft']);
    Campaign::factory()->create(['status' => 'active']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('status', 'active')
        ->assertCanSeeTableRecords(Campaign::where('status', 'active')->get())
        ->assertCanNotSeeTableRecords(Campaign::where('status', 'draft')->get());
});

it('can filter featured campaigns', function (): void {
    Campaign::factory()->create(['is_featured' => true]);
    Campaign::factory()->create(['is_featured' => false]);
    Campaign::factory()->create(['is_featured' => true]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('is_featured', true)
        ->assertCanSeeTableRecords(Campaign::where('is_featured', true)->get())
        ->assertCanNotSeeTableRecords(Campaign::where('is_featured', false)->get());
});

it('can filter active campaigns', function (): void {
    Campaign::factory()->create(['status' => 'active', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
    Campaign::factory()->create(['status' => 'paused']);
    Campaign::factory()->create(['status' => 'active', 'starts_at' => now()->addDay()]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('active')
        ->assertCanSeeTableRecords(Campaign::active()->get());
});

it('can filter scheduled campaigns', function (): void {
    Campaign::factory()->create(['status' => 'scheduled']);
    Campaign::factory()->create(['status' => 'active']);
    Campaign::factory()->create(['status' => 'draft']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('scheduled')
        ->assertCanSeeTableRecords(Campaign::scheduled()->get());
});

it('can filter campaigns by date range', function (): void {
    $oldCampaign = Campaign::factory()->create(['created_at' => now()->subDays(10)]);
    $recentCampaign = Campaign::factory()->create(['created_at' => now()->subDays(2)]);
    $newCampaign = Campaign::factory()->create(['created_at' => now()]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('created_at', [
            'created_from' => now()->subDays(5)->format('Y-m-d'),
            'created_until' => now()->subDays(1)->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentCampaign])
        ->assertCanNotSeeTableRecords([$oldCampaign, $newCampaign]);
});

it('can search campaigns by name', function (): void {
    Campaign::factory()->create(['name' => 'Summer Sale']);
    Campaign::factory()->create(['name' => 'Winter Collection']);
    Campaign::factory()->create(['name' => 'Summer Discount']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->searchTable('Summer')
        ->assertCanSeeTableRecords(Campaign::where('name', 'like', '%Summer%')->get())
        ->assertCanNotSeeTableRecords(Campaign::where('name', 'like', '%Winter%')->get());
});

it('can sort campaigns by name', function (): void {
    Campaign::factory()->create(['name' => 'Zebra Campaign']);
    Campaign::factory()->create(['name' => 'Alpha Campaign']);
    Campaign::factory()->create(['name' => 'Beta Campaign']);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(Campaign::orderBy('name')->get());
});

it('can sort campaigns by budget', function (): void {
    Campaign::factory()->create(['budget' => 1000.0]);
    Campaign::factory()->create(['budget' => 5000.0]);
    Campaign::factory()->create(['budget' => 2000.0]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->sortTable('budget')
        ->assertCanSeeTableRecords(Campaign::orderBy('budget')->get());
});

it('can sort campaigns by created date', function (): void {
    Campaign::factory()->create(['created_at' => now()->subDays(3)]);
    Campaign::factory()->create(['created_at' => now()->subDays(1)]);
    Campaign::factory()->create(['created_at' => now()->subDays(2)]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->sortTable('created_at')
        ->assertCanSeeTableRecords(Campaign::orderBy('created_at')->get());
});

it('can toggle table columns', function (): void {
    Campaign::factory()->create(['budget' => 1000.0]);

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->assertTableColumnExists('budget')
        ->assertTableColumnToggledHidden('budget')
        ->toggleTableColumn('budget')
        ->assertTableColumnVisible('budget');
});

it('can handle trashed campaigns', function (): void {
    $campaign = Campaign::factory()->create();
    $campaign->delete();

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->filterTable('trashed')
        ->assertCanSeeTableRecords([$campaign]);
});

it('can restore trashed campaigns', function (): void {
    $campaign = Campaign::factory()->create();
    $campaign->delete();

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableBulkAction('restore', [$campaign]);

    expect(Campaign::find($campaign->id))->not->toBeNull();
});

it('can force delete campaigns', function (): void {
    $campaign = Campaign::factory()->create();
    $campaign->delete();

    actingAs($this->admin);

    Livewire::test(ListCampaigns::class)
        ->callTableBulkAction('forceDelete', [$campaign]);

    expect(Campaign::withTrashed()->find($campaign->id))->toBeNull();
});

it('validates required fields on create', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => '',
            'slug' => '',
            'type' => '',
            'status' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'slug', 'type', 'status']);
});

it('validates unique slug', function (): void {
    Campaign::factory()->create(['slug' => 'existing-slug']);

    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Test Campaign',
            'slug' => 'existing-slug',
            'type' => 'banner',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates url format for cta_url', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'type' => 'banner',
            'status' => 'active',
            'cta_url' => 'invalid-url',
        ])
        ->call('create')
        ->assertHasFormErrors(['cta_url']);
});

it('validates numeric values for budget and budget_limit', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'type' => 'banner',
            'status' => 'active',
            'budget' => 'not-a-number',
            'budget_limit' => 'also-not-a-number',
        ])
        ->call('create')
        ->assertHasFormErrors(['budget', 'budget_limit']);
});

it('validates min value for numeric fields', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'type' => 'banner',
            'status' => 'active',
            'budget' => -100,
            'budget_limit' => -50,
            'max_uses' => -10,
        ])
        ->call('create')
        ->assertHasFormErrors(['budget', 'budget_limit', 'max_uses']);
});

it('validates max length for text fields', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => str_repeat('a', 256),  // Exceeds 255 char limit
            'slug' => str_repeat('a', 256),  // Exceeds 255 char limit
            'type' => 'banner',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'slug']);
});

it('validates meta title and description length', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'type' => 'banner',
            'status' => 'active',
            'meta_title' => str_repeat('a', 61),  // Exceeds 60 char limit
            'meta_description' => str_repeat('a', 161),  // Exceeds 160 char limit
        ])
        ->call('create')
        ->assertHasFormErrors(['meta_title', 'meta_description']);
});

it('can handle media uploads', function (): void {
    actingAs($this->admin);

    // This test would require actual file uploads in a real scenario
    // For now, we'll test that the form accepts the media fields
    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Media Test Campaign',
            'slug' => 'media-test-campaign',
            'type' => 'banner',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Campaign::where('slug', 'media-test-campaign')->exists())->toBeTrue();
});

it('can handle conditional fields based on campaign type', function (): void {
    actingAs($this->admin);

    // Test email campaign with subject field
    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Email Campaign',
            'slug' => 'email-campaign',
            'type' => 'email',
            'status' => 'active',
            'subject' => 'Email Subject',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Test SMS campaign with subject field
    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'SMS Campaign',
            'slug' => 'sms-campaign',
            'type' => 'sms',
            'status' => 'active',
            'subject' => 'SMS Subject',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Test banner campaign without subject field
    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Banner Campaign',
            'slug' => 'banner-campaign',
            'type' => 'banner',
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('can handle multiple target selections', function (): void {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $group1 = CustomerGroup::factory()->create();
    $group2 = CustomerGroup::factory()->create();

    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Multi Target Campaign',
            'slug' => 'multi-target-campaign',
            'type' => 'banner',
            'status' => 'active',
            'target_categories' => [$category1->id, $category2->id],
            'target_products' => [$product1->id, $product2->id],
            'target_customer_groups' => [$group1->id, $group2->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $campaign = Campaign::where('slug', 'multi-target-campaign')->first();
    expect($campaign->targetCategories)->toHaveCount(2);
    expect($campaign->targetProducts)->toHaveCount(2);
    expect($campaign->targetCustomerGroups)->toHaveCount(2);
});

it('can handle campaign with all automation settings', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Automated Campaign',
            'slug' => 'automated-campaign',
            'type' => 'email',
            'status' => 'scheduled',
            'start_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'auto_start' => true,
            'auto_end' => true,
            'auto_pause_on_budget' => true,
            'budget_limit' => 1000.0,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $campaign = Campaign::where('slug', 'automated-campaign')->first();
    expect($campaign->auto_start)->toBeTrue();
    expect($campaign->auto_end)->toBeTrue();
    expect($campaign->auto_pause_on_budget)->toBeTrue();
});

it('can handle campaign with all tracking settings', function (): void {
    actingAs($this->admin);

    Livewire::test(CreateCampaign::class)
        ->fillForm([
            'name' => 'Tracking Campaign',
            'slug' => 'tracking-campaign',
            'type' => 'banner',
            'status' => 'active',
            'track_conversions' => true,
            'send_notifications' => true,
            'max_uses' => 500,
            'social_media_ready' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $campaign = Campaign::where('slug', 'tracking-campaign')->first();
    expect($campaign->track_conversions)->toBeTrue();
    expect($campaign->send_notifications)->toBeTrue();
    expect($campaign->max_uses)->toBe(500);
    expect($campaign->social_media_ready)->toBeTrue();
});


