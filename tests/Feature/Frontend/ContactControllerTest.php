<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Jobs\SendContactMessageJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_accessible(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_localized_contact_page_is_accessible(): void
    {
        $this->get('/lt/contact')->assertOk();
    }

    public function test_contact_form_submission_is_saved_and_dispatched_to_mail_job(): void
    {
        Queue::fake();

        $payload = [
            'name'         => 'Jonas Petrauskas',
            'email'        => 'jonas@example.com',
            'phone'        => '+37061234567',
            'order_number' => 'ORD-2026-0001',
            'subject'      => 'Need project consultation',
            'message'      => 'Please contact me about facade insulation materials.',
        ];

        $response = $this->post(route('frontend.contact.send'), $payload);

        $response
            ->assertRedirect()
            ->assertSessionHas('success', __('frontend.contact.flash.success'));

        $this->assertDatabaseHas('contact_messages', [
            'name'         => $payload['name'],
            'email'        => $payload['email'],
            'phone'        => $payload['phone'],
            'order_number' => $payload['order_number'],
            'subject'      => $payload['subject'],
            'message'      => $payload['message'],
            'ip_address'   => '127.0.0.1',
        ]);

        Queue::assertPushed(SendContactMessageJob::class, 1);
    }

    public function test_admin_contact_messages_page_route_is_registered(): void
    {
        $this->assertTrue(Route::has('filament.admin.resources.contact-messages.index'));
        $this->assertSame('/admin/contact-messages', route('filament.admin.resources.contact-messages.index', absolute: false));
    }
}
