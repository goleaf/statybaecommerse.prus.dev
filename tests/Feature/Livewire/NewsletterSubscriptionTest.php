<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\NewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_custom_source_values_and_resets_form_state(): void
    {
        // Boot a Livewire component instance so we can exercise the subscription flow end-to-end.
        $component = Livewire::test(NewsletterSubscription::class);

        // Set a custom acquisition source to ensure the component forwards the override to the model helper.
        $component->call('setSource', 'popup-widget');

        $component
            // Provide the minimum data required for a valid subscription alongside optional profile fields.
            ->set('email', 'popup.subscriber@example.test')
            ->set('first_name', 'Popup')
            ->set('last_name', 'Subscriber')
            ->set('company', 'OptIn Corp')
            ->set('interests', ['promotions'])
            ->call('subscribe');

        // Assert the subscriber record exists with the custom source to verify the override flowed through persistence.
        $this->assertDatabaseHas('subscribers', [
            'email'  => 'popup.subscriber@example.test',
            'source' => 'popup-widget',
            'status' => 'active',
        ]);

        // Refresh the subscriber to confirm interest metadata persisted as expected.
        $subscriber = Subscriber::query()->where('email', 'popup.subscriber@example.test')->first();
        $this->assertSame(['promotions'], $subscriber?->interests ?? []);

        // Confirm the component reset the captured form fields so the frontend renders a clean state post-submit.
        $this->assertSame('', $component->get('email'));
        $this->assertSame('', $component->get('first_name'));
        $this->assertSame('', $component->get('last_name'));
        $this->assertSame('', $component->get('company'));
        $this->assertSame([], $component->get('interests'));
        $this->assertTrue($component->get('isSubscribed'));
        $this->assertTrue($component->get('showSuccess'));
    }
}
