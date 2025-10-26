<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsletterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_subscribes_new_email_via_json(): void
    {
        $response = $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'jane.doe@example.com',
        ]);

        $response->assertOk()->assertJson([
            'status'  => 'success',
            'message' => __('newsletter.subscribed_successfully'),
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => 'jane.doe@example.com',
            'status' => 'active',
        ]);
    }

    public function test_subscribe_requires_valid_email(): void
    {
        $response = $this->postJson(route('frontend.newsletter.subscribe'), [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)->assertJson([
            'status' => 'error',
        ])->assertJsonValidationErrors(['email']);
    }

    public function test_it_unsubscribes_existing_subscriber(): void
    {
        $subscriber = Subscriber::factory()->active()->create([
            'email' => 'john.doe@example.com',
        ]);

        $response = $this->postJson(route('frontend.newsletter.unsubscribe'), [
            'email' => $subscriber->email,
        ]);

        $response->assertOk()->assertJson([
            'status'  => 'success',
            'message' => __('subscribers.unsubscribed_successfully'),
        ]);

        $this->assertDatabaseHas('subscribers', [
            'email'  => $subscriber->email,
            'status' => 'unsubscribed',
        ]);
    }

    public function test_unsubscribe_requires_valid_email(): void
    {
        $response = $this->postJson(route('frontend.newsletter.unsubscribe'), []);

        $response->assertStatus(422)->assertJson([
            'status' => 'error',
        ])->assertJsonValidationErrors(['email']);
    }
}
