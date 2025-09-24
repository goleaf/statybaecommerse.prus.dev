<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\NavigationGroup;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NotificationTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_can_list_notification_templates(): void
    {
        $this->actingAs($this->adminUser);

        $template = NotificationTemplate::factory()->create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'type' => 'email',
            'event' => 'user_registered',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates::class)
            ->assertCanSeeTableRecords(NotificationTemplate::all());
    }

    public function test_can_create_notification_template(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'test-template',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
                'variables' => 'name,email',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notification_templates', [
            'name' => 'Test Template',
            'slug' => 'test-template',
            'type' => 'email',
            'event' => 'user_registered',
            'is_active' => true,
        ]);
    }

    public function test_can_edit_notification_template(): void
    {
        $this->actingAs($this->adminUser);

        $template = NotificationTemplate::factory()->create([
            'name' => 'Original Template',
            'slug' => 'original-template',
            'type' => 'email',
            'event' => 'user_registered',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\EditNotificationTemplate::class, [
            'record' => $template->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Template',
                'subject' => 'Updated Subject',
                'content' => 'Updated content',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('notification_templates', [
            'id' => $template->id,
            'name' => 'Updated Template',
        ]);
    }

    public function test_can_view_notification_template(): void
    {
        $this->actingAs($this->adminUser);

        $template = NotificationTemplate::factory()->create([
            'name' => 'Test Template',
            'slug' => 'test-template',
            'type' => 'email',
            'event' => 'user_registered',
            'subject' => 'Welcome!',
            'content' => 'Welcome to our platform!',
            'is_active' => true,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\ViewNotificationTemplate::class, [
            'record' => $template->getRouteKey(),
        ])
            ->assertCanSeeFormData([
                'name' => 'Test Template',
                'slug' => 'test-template',
            ]);
    }

    public function test_can_delete_notification_template(): void
    {
        $this->actingAs($this->adminUser);

        $template = NotificationTemplate::factory()->create([
            'name' => 'Test Template',
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates::class)
            ->callTableAction('delete', $template);

        $this->assertDatabaseMissing('notification_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_can_filter_by_type(): void
    {
        $this->actingAs($this->adminUser);

        $emailTemplate = NotificationTemplate::factory()->create([
            'type' => 'email',
        ]);
        $smsTemplate = NotificationTemplate::factory()->create([
            'type' => 'sms',
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates::class)
            ->filterTable('type', 'email')
            ->assertCanSeeTableRecords([$emailTemplate])
            ->assertCanNotSeeTableRecords([$smsTemplate]);
    }

    public function test_can_filter_by_active_status(): void
    {
        $this->actingAs($this->adminUser);

        $activeTemplate = NotificationTemplate::factory()->create([
            'is_active' => true,
        ]);
        $inactiveTemplate = NotificationTemplate::factory()->create([
            'is_active' => false,
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates::class)
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$activeTemplate])
            ->assertCanNotSeeTableRecords([$inactiveTemplate]);
    }

    public function test_auto_generates_slug_from_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template Name',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->assertFormSet('slug', 'test-template-name');
    }

    public function test_validation_requires_name(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_validation_requires_slug(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_validation_requires_type(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'test-template',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['type']);
    }

    public function test_validation_requires_event(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'test-template',
                'type' => 'email',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['event']);
    }

    public function test_validation_requires_subject(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'test-template',
                'type' => 'email',
                'event' => 'user_registered',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['subject']);
    }

    public function test_validation_requires_content(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'test-template',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
            ])
            ->call('create')
            ->assertHasFormErrors(['content']);
    }

    public function test_validation_slug_must_be_unique(): void
    {
        $this->actingAs($this->adminUser);

        NotificationTemplate::factory()->create([
            'slug' => 'existing-template',
        ]);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'existing-template',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_validation_slug_must_be_alpha_dash(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(\App\Filament\Resources\NotificationTemplateResource\Pages\CreateNotificationTemplate::class)
            ->fillForm([
                'name' => 'Test Template',
                'slug' => 'invalid slug!',
                'type' => 'email',
                'event' => 'user_registered',
                'subject' => 'Welcome!',
                'content' => 'Welcome to our platform!',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_navigation_group_is_content(): void
    {
        $this->assertEquals(NavigationGroup::Content, \App\Filament\Resources\NotificationTemplateResource::getNavigationGroup());
    }

    public function test_has_correct_navigation_sort(): void
    {
        $this->assertEquals(6, \App\Filament\Resources\NotificationTemplateResource::getNavigationSort());
    }

    public function test_has_correct_record_title_attribute(): void
    {
        $this->assertEquals('name', \App\Filament\Resources\NotificationTemplateResource::getRecordTitleAttribute());
    }
}
