<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Subscriber;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;

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

    public function mount(): void
    {
        $this->resetForm();
    }

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
                $subscriberData = [
                    'email' => $this->email,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'company' => $this->company,
                    'interests' => $this->interests,
                    'source' => $this->source,
                    'status' => 'active',
                ];

                Subscriber::subscribe($subscriberData);
                
                $this->isSubscribed = true;
                $this->showSuccess = true;
                
                session()->flash('success', __('newsletter.subscribed_successfully'));
                
                // Dispatch event for other components to listen
                $this->dispatch('subscriber-added', [
                    'email' => $this->email,
                    'name' => trim($this->first_name . ' ' . $this->last_name),
                ]);
            }

            $this->resetForm();
            
        } catch (\Exception $e) {
            Log::error('Newsletter subscription error: ' . $e->getMessage());
            session()->flash('error', __('newsletter.subscription_error'));
        }
    }

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

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function render()
    {
        return view('livewire.newsletter-subscription');
    }
}
