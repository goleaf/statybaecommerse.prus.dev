<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendContactMessageJob;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_displays_form(): void
    {
        Company::factory()->active()->create([
            'address' => 'Konstitucijos pr. 7, Vilnius',
            'phone'   => '+37060000000',
        ]);

        SystemSetting::factory()->active()->public()->create([
            'key'   => 'mail.support_email',
            'value' => 'support@example.test',
            'type'  => 'email',
            'group' => 'email',
        ]);

        $response = $this->get(route('frontend.contact.index'));

        $response->assertStatus(200);
        $response->assertSee(__('frontend/contact.form.submit'));
        $response->assertSee('support@example.test');
    }

    public function test_contact_form_submission_creates_record_and_dispatches_job(): void
    {
        Queue::fake();

        $payload = [
            'name'         => 'John Doe',
            'email'        => 'john@example.com',
            'subject'      => 'Order question',
            'message'      => 'I would like to know more about my order.',
            'phone'        => '+37060000000',
            'order_number' => 'ORD-1001',
        ];

        $response = $this->post(route('frontend.contact.send'), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success', __('frontend/contact.flash.success'));

        $this->assertDatabaseHas('contact_messages', [
            'email'        => 'john@example.com',
            'subject'      => 'Order question',
            'order_number' => 'ORD-1001',
        ]);

        $contactMessage = ContactMessage::first();
        $this->assertNotNull($contactMessage);
        $this->assertSame('John Doe', $contactMessage->name);

        Queue::assertPushed(SendContactMessageJob::class, function (SendContactMessageJob $job) use ($contactMessage): bool {
            return $job->contactMessage->is($contactMessage);
        });
    }
}
