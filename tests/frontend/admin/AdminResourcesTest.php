<?php

declare(strict_types=1);

use App\Filament\Resources\AnalyticsEventResource;
use App\Filament\Resources\AnalyticsResource;
use App\Filament\Resources\CampaignResource;
use App\Filament\Resources\NotificationResource;
use App\Filament\Resources\PartnerTierResource;
use App\Filament\Resources\SystemSettingsResource;
use App\Models\AnalyticsEvent;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\PartnerTier;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');
});

describe('CampaignResource', function (): void {
    it('can render campaign resource index page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->assertOk();
    });

    it('can render campaign resource create page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->assertOk();
    });

    it('can render campaign resource view and edit pages', function (): void {
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'slug' => 'test-campaign',
            'status' => 'draft',
        ]);

        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ViewCampaign::class, ['record' => $campaign->getKey()])
            ->assertOk();
        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\EditCampaign::class, ['record' => $campaign->getKey()])
            ->assertOk();
    });

    it('can create a campaign via filament form', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
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

    it('can edit a campaign via filament form', function (): void {
        $campaign = Campaign::factory()->create([
            'name' => 'Original Campaign',
            'status' => 'draft',
        ]);

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\EditCampaign::class, ['record' => $campaign->getKey()])
            ->fillForm([
                'name' => 'Updated Campaign',
                'status' => 'active',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $campaign->refresh();
        expect($campaign->name)->toBe('Updated Campaign');
        expect($campaign->status)->toBe('active');
    });

    it('can delete a campaign', function (): void {
        $campaign = Campaign::factory()->create();

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\ListCampaigns::class)
            ->callTableAction('delete', $campaign)
            ->assertHasNoTableActionErrors();

        expect(Campaign::find($campaign->id))->toBeNull();
    });

    it('lists campaigns and shows names', function (): void {
        $a = Campaign::factory()->create(['status' => 'active', 'name' => 'Summer Blast']);
        $b = Campaign::factory()->create(['status' => 'draft', 'name' => 'Quiet Launch']);

        actingAs($this->admin)
            ->get(CampaignResource::getUrl('index'))
            ->assertSee('Summer Blast')
            ->assertSee('Quiet Launch');
    });

    it('validates required fields when creating campaign', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\CampaignResource\Pages\CreateCampaign::class)
            ->fillForm([
                'name' => '', // Required field empty
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    });
});

describe('SystemSettingsResource', function (): void {
    it('can render system settings resource index page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\ListSystemSettings::class)
            ->assertOk();
    });

    it('can render system settings resource create page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->assertOk();
    });

    it('can create a system setting via filament form', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\CreateSystemSetting::class)
            ->fillForm([
                'key' => 'test_setting',
                'value' => 'test_value',
                'display_name' => 'Test Setting',
                'description' => 'Test description',
                'type' => 'string',
                'is_public' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Setting::query()->where('key', 'test_setting')->exists())->toBeTrue();
    });

    it('can edit a system setting via filament form', function (): void {
        $setting = Setting::factory()->create([
            'key' => 'original_setting',
            'value' => 'original_value',
        ]);

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\EditSystemSetting::class, ['record' => $setting->getKey()])
            ->fillForm([
                'value' => 'updated_value',
                'display_name' => 'Updated Setting',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $setting->refresh();
        expect($setting->value)->toBe('updated_value');
        expect($setting->display_name)->toBe('Updated Setting');
    });

    it('can delete a system setting', function (): void {
        $setting = Setting::factory()->create();

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\SystemSettingsResource\Pages\ListSystemSettings::class)
            ->callTableAction('delete', $setting)
            ->assertHasNoTableActionErrors();

        expect(Setting::find($setting->id))->toBeNull();
    });

    it('lists system settings and shows keys', function (): void {
        $a = Setting::factory()->create(['key' => 'setting_a', 'display_name' => 'Setting A']);
        $b = Setting::factory()->create(['key' => 'setting_b', 'display_name' => 'Setting B']);

        actingAs($this->admin)
            ->get(SystemSettingsResource::getUrl('index'))
            ->assertSee('Setting A')
            ->assertSee('Setting B');
    });
});

describe('NotificationResource', function (): void {
    it('can render notification resource index page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->assertOk();
    });

    it('can render notification resource create page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->assertOk();
    });

    it('can create a notification via filament form', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\CreateNotification::class)
            ->fillForm([
                'type' => 'info',
                'title' => 'Test Notification',
                'message' => 'This is a test notification',
                'user_id' => $this->admin->id,
                'is_read' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(Notification::query()->where('title', 'Test Notification')->exists())->toBeTrue();
    });

    it('can edit a notification via filament form', function (): void {
        $notification = Notification::factory()->create([
            'title' => 'Original Title',
            'is_read' => false,
        ]);

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\EditNotification::class, ['record' => $notification->getKey()])
            ->fillForm([
                'title' => 'Updated Title',
                'is_read' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $notification->refresh();
        expect($notification->title)->toBe('Updated Title');
        expect($notification->is_read)->toBeTrue();
    });

    it('can delete a notification', function (): void {
        $notification = Notification::factory()->create();

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\NotificationResource\Pages\ListNotifications::class)
            ->callTableAction('delete', $notification)
            ->assertHasNoTableActionErrors();

        expect(Notification::find($notification->id))->toBeNull();
    });

    it('lists notifications and shows titles', function (): void {
        $a = Notification::factory()->create(['title' => 'Notification A']);
        $b = Notification::factory()->create(['title' => 'Notification B']);

        actingAs($this->admin)
            ->get(NotificationResource::getUrl('index'))
            ->assertSee('Notification A')
            ->assertSee('Notification B');
    });
});

describe('AnalyticsEventResource', function (): void {
    it('can render analytics event resource index page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->assertOk();
    });

    it('can render analytics event resource view page', function (): void {
        $event = AnalyticsEvent::factory()->create();

        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\AnalyticsEventResource\Pages\ViewAnalyticsEvent::class, ['record' => $event->getKey()])
            ->assertOk();
    });

    it('can create an analytics event via filament form', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\AnalyticsEventResource\Pages\CreateAnalyticsEvent::class)
            ->fillForm([
                'event_name' => 'test_event',
                'event_type' => 'page_view',
                'user_id' => $this->admin->id,
                'session_id' => 'test_session_123',
                'properties' => ['page' => '/test'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(AnalyticsEvent::query()->where('event_name', 'test_event')->exists())->toBeTrue();
    });

    it('can edit an analytics event via filament form', function (): void {
        $event = AnalyticsEvent::factory()->create([
            'event_name' => 'original_event',
            'event_type' => 'click',
        ]);

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\AnalyticsEventResource\Pages\EditAnalyticsEvent::class, ['record' => $event->getKey()])
            ->fillForm([
                'event_name' => 'updated_event',
                'event_type' => 'page_view',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $event->refresh();
        expect($event->event_name)->toBe('updated_event');
        expect($event->event_type)->toBe('page_view');
    });

    it('can delete an analytics event', function (): void {
        $event = AnalyticsEvent::factory()->create();

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\AnalyticsEventResource\Pages\ListAnalyticsEvents::class)
            ->callTableAction('delete', $event)
            ->assertHasNoTableActionErrors();

        expect(AnalyticsEvent::find($event->id))->toBeNull();
    });

    it('lists analytics events and shows event names', function (): void {
        $a = AnalyticsEvent::factory()->create(['event_name' => 'Event A']);
        $b = AnalyticsEvent::factory()->create(['event_name' => 'Event B']);

        actingAs($this->admin)
            ->get(AnalyticsEventResource::getUrl('index'))
            ->assertSee('Event A')
            ->assertSee('Event B');
    });
});

describe('PartnerTierResource', function (): void {
    it('can render partner tier resource index page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->assertOk();
    });

    it('can render partner tier resource create page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->assertOk();
    });

    it('can create a partner tier via filament form', function (): void {
        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\CreatePartnerTier::class)
            ->fillForm([
                'name' => 'Gold Partner',
                'description' => 'Gold tier partner',
                'discount_percentage' => 15.0,
                'min_order_value' => 1000.0,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(PartnerTier::query()->where('name', 'Gold Partner')->exists())->toBeTrue();
    });

    it('can edit a partner tier via filament form', function (): void {
        $tier = PartnerTier::factory()->create([
            'name' => 'Original Tier',
            'discount_percentage' => 10.0,
        ]);

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\EditPartnerTier::class, ['record' => $tier->getKey()])
            ->fillForm([
                'name' => 'Updated Tier',
                'discount_percentage' => 20.0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $tier->refresh();
        expect($tier->name)->toBe('Updated Tier');
        expect($tier->discount_percentage)->toBe(20.0);
    });

    it('can delete a partner tier', function (): void {
        $tier = PartnerTier::factory()->create();

        actingAs($this->admin);

        Livewire::test(\App\Filament\Resources\PartnerTierResource\Pages\ListPartnerTiers::class)
            ->callTableAction('delete', $tier)
            ->assertHasNoTableActionErrors();

        expect(PartnerTier::find($tier->id))->toBeNull();
    });

    it('lists partner tiers and shows names', function (): void {
        $a = PartnerTier::factory()->create(['name' => 'Tier A']);
        $b = PartnerTier::factory()->create(['name' => 'Tier B']);

        actingAs($this->admin)
            ->get(PartnerTierResource::getUrl('index'))
            ->assertSee('Tier A')
            ->assertSee('Tier B');
    });
});

describe('AnalyticsResource', function (): void {
    it('can render analytics resource dashboard page', function (): void {
        actingAs($this->admin);
        Livewire::test(\App\Filament\Resources\AnalyticsResource\Pages\AnalyticsDashboard::class)
            ->assertOk();
    });

    it('shows analytics dashboard with data', function (): void {
        // Create some test data
        AnalyticsEvent::factory()->count(5)->create();
        Campaign::factory()->count(3)->create();

        actingAs($this->admin)
            ->get(AnalyticsResource::getUrl('index'))
            ->assertOk();
    });
});

describe('Admin Access Control', function (): void {
    it('denies access to non-admin users', function (): void {
        $user = User::factory()->create();

        actingAs($user)
            ->get(CampaignResource::getUrl('index'))
            ->assertStatus(403);
    });

    it('allows access to admin users', function (): void {
        actingAs($this->admin)
            ->get(CampaignResource::getUrl('index'))
            ->assertOk();
    });
});

describe('Resource Navigation', function (): void {
    it('can access all resource index pages', function (): void {
        actingAs($this->admin);

        $resources = [
            CampaignResource::class,
            SystemSettingsResource::class,
            NotificationResource::class,
            AnalyticsEventResource::class,
            PartnerTierResource::class,
            AnalyticsResource::class,
        ];

        foreach ($resources as $resource) {
            $this->get($resource::getUrl('index'))
                ->assertOk();
        }
    });
});
