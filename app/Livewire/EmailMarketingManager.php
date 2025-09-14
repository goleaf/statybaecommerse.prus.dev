<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Subscriber;
use App\Models\Company;
use App\Services\EmailMarketingService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;

final class EmailMarketingManager extends Component
{
    public array $mailchimpStats = [];
    public array $syncResults = [];
    public bool $isSyncing = false;
    public bool $showStats = false;

    #[Validate('required|string|max:255')]
    public string $campaignSubject = '';

    #[Validate('required|string|max:255')]
    public string $campaignTitle = '';

    #[Validate('required|string|max:255')]
    public string $fromName = '';

    #[Validate('required|email|max:255')]
    public string $replyTo = '';

    public string $selectedInterest = '';
    public string $selectedCompany = '';

    protected $listeners = ['refreshStats'];

    public function mount(): void
    {
        $this->fromName = config('app.name', 'Our Company');
        $this->replyTo = config('mail.from.address', 'noreply@example.com');
        $this->loadMailchimpStats();
    }

    public function loadMailchimpStats(): void
    {
        try {
            $emailService = new EmailMarketingService();
            $this->mailchimpStats = $emailService->getMailchimpStats() ?? [];
            $this->showStats = true;
        } catch (\Exception $e) {
            Log::error('Failed to load Mailchimp stats', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to load Mailchimp statistics. Please check your configuration.');
        }
    }

    public function syncAllSubscribers(): void
    {
        $this->isSyncing = true;
        
        try {
            $emailService = new EmailMarketingService();
            $this->syncResults = $emailService->bulkSyncSubscribers();
            
            session()->flash('success', sprintf(
                'Sync completed! %d subscribers synced successfully, %d failed.',
                $this->syncResults['success'],
                $this->syncResults['failed']
            ));
            
            $this->loadMailchimpStats();
        } catch (\Exception $e) {
            Log::error('Failed to sync subscribers', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to sync subscribers. Please try again.');
        } finally {
            $this->isSyncing = false;
        }
    }

    public function createCampaign(): void
    {
        $this->validate();

        try {
            $emailService = new EmailMarketingService();
            
            $campaignData = [
                'subject' => $this->campaignSubject,
                'title' => $this->campaignTitle,
                'from_name' => $this->fromName,
                'reply_to' => $this->replyTo,
            ];

            if ($this->selectedInterest) {
                $segmentId = $emailService->createInterestSegment($this->selectedInterest);
                if ($segmentId) {
                    $campaignData['segment_id'] = $segmentId;
                }
            }

            $campaignId = $emailService->createCampaign($campaignData);
            
            if ($campaignId) {
                session()->flash('success', 'Campaign created successfully!');
                $this->resetForm();
            } else {
                session()->flash('error', 'Failed to create campaign. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to create campaign', ['error' => $e->getMessage()]);
            session()->flash('error', 'Failed to create campaign. Please try again.');
        }
    }

    public function createInterestSegment(string $interest): void
    {
        try {
            $emailService = new EmailMarketingService();
            $segmentId = $emailService->createInterestSegment($interest);
            
            if ($segmentId) {
                session()->flash('success', "Interest segment for '{$interest}' created successfully!");
            } else {
                session()->flash('error', "Failed to create interest segment for '{$interest}'.");
            }
        } catch (\Exception $e) {
            Log::error('Failed to create interest segment', [
                'interest' => $interest,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', "Failed to create interest segment for '{$interest}'.");
        }
    }

    public function refreshStats(): void
    {
        $this->loadMailchimpStats();
    }

    private function resetForm(): void
    {
        $this->campaignSubject = '';
        $this->campaignTitle = '';
        $this->selectedInterest = '';
        $this->selectedCompany = '';
    }

    public function getInterestsProperty(): array
    {
        return [
            'products' => 'Products',
            'news' => 'News & Updates',
            'promotions' => 'Promotions & Discounts',
            'events' => 'Events',
            'blog' => 'Blog Posts',
            'technical' => 'Technical Updates',
            'business' => 'Business News',
            'support' => 'Support & Help',
        ];
    }

    public function getCompaniesProperty()
    {
        return Company::active()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.email-marketing-manager');
    }
}
