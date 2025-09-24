<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * NewsletterSubscription
 *
 * Livewire component for NewsletterSubscription with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $company
 * @property array $interests
 * @property bool $isSubscribed
 * @property bool $showSuccess
 * @property string $source
 * @property mixed $listeners
 */
final class NewsletterSubscription extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:255')]
    public string $first_name = '';

    #[Validate('nullable|string|max:255')]
    public string $last_name = '';

    #[Validate('nullable|string|max:255')]
    public string $company = '';

    #[Validate('nullable|array')]
    public array $interests = [];

    public bool $isSubscribed = false;

    public bool $showSuccess = false;

    public string $source = 'website';

    protected $listeners = ['resetForm'];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->resetForm();
    }

    /**
     * Handle subscribe functionality with proper error handling.
     */
    public function subscribe(): void
    {
        $this->validate();
        try {
            // Check if email already exists
            $existingSubscriber = Subscriber::findByEmail($this->email);
            if ($existingSubscriber) {
                if ($existingSubscriber->status === 'unsubscribed') {
                    // Resubscribe
                    $existingSubscriber->resubscribe();
                    $this->isSubscribed = true;
                    $this->showSuccess = true;
                    session()->flash('success', __('newsletter.resubscribed_successfully'));
                } else {
                    // Already subscribed
                    $this->isSubscribed = true;
                    session()->flash('info', __('newsletter.already_subscribed'));
                }
            } else {
                // Create new subscriber
                $subscriberData = ['email' => $this->email, 'first_name' => $this->first_name, 'last_name' => $this->last_name, 'company' => $this->company, 'interests' => $this->interests, 'source' => $this->source, 'status' => 'active'];
                Subscriber::subscribe($subscriberData);
                $this->isSubscribed = true;
                $this->showSuccess = true;
                session()->flash('success', __('newsletter.subscribed_successfully'));
                // Dispatch event for other components to listen
                $this->dispatch('subscriber-added', ['email' => $this->email, 'name' => trim($this->first_name.' '.$this->last_name)]);
            }
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error: '.$e->getMessage());
            session()->flash('error', __('newsletter.subscription_error'));
        }
    }

    /**
     * Handle resetForm functionality with proper error handling.
     */
    public function resetForm(): void
    {
        $this->email = '';
        $this->first_name = '';
        $this->last_name = '';
        $this->company = '';
        $this->interests = [];
        $this->isSubscribed = false;
        $this->showSuccess = false;
    }

    /**
     * Handle setSource functionality with proper error handling.
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.newsletter-subscription');
    }
}
