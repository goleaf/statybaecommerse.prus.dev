<?php

declare (strict_types=1);
namespace App\Services;

use App\Models\Subscriber;
use App\Models\Company;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
/**
 * EmailMarketingService
 * 
 * Service class containing EmailMarketingService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 * @property string $apiKey
 * @property string $baseUrl
 * @property string $listId
 */
final class EmailMarketingService
{
    private string $apiKey;
    private string $baseUrl;
    private string $listId;
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct()
    {
        $this->apiKey = config('services.mailchimp.api_key') ?? '';
        $this->baseUrl = config('services.mailchimp.base_url') ?? 'https://us1.api.mailchimp.com/3.0';
        $this->listId = config('services.mailchimp.list_id') ?? '';
    }
    /**
     * Handle syncSubscriberToMailchimp functionality with proper error handling.
     * @param Subscriber $subscriber
     * @return bool
     */
    public function syncSubscriberToMailchimp(Subscriber $subscriber): bool
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'])->post("{$this->baseUrl}/lists/{$this->listId}/members", ['email_address' => $subscriber->email, 'status' => $this->mapStatusToMailchimp($subscriber->status), 'merge_fields' => ['FNAME' => $subscriber->first_name, 'LNAME' => $subscriber->last_name, 'COMPANY' => $subscriber->company ?? '', 'PHONE' => $subscriber->phone ?? ''], 'tags' => $subscriber->interests ?? [], 'marketing_permissions' => [['marketing_permission_id' => 'email', 'enabled' => $subscriber->status === 'active']]]);
            if ($response->successful()) {
                Log::info('Subscriber synced to Mailchimp', ['subscriber_id' => $subscriber->id, 'email' => $subscriber->email]);
                return true;
            }
            Log::error('Failed to sync subscriber to Mailchimp', ['subscriber_id' => $subscriber->id, 'response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception syncing subscriber to Mailchimp', ['subscriber_id' => $subscriber->id, 'error' => $e->getMessage()]);
            return false;
        }
    }
    /**
     * Handle createCampaign functionality with proper error handling.
     * @param array $campaignData
     * @return string|null
     */
    public function createCampaign(array $campaignData): ?string
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'])->post("{$this->baseUrl}/campaigns", ['type' => 'regular', 'recipients' => ['list_id' => $this->listId, 'segment_opts' => ['saved_segment_id' => $campaignData['segment_id'] ?? null]], 'settings' => ['subject_line' => $campaignData['subject'], 'from_name' => $campaignData['from_name'] ?? config('app.name'), 'reply_to' => $campaignData['reply_to'] ?? config('mail.from.address'), 'title' => $campaignData['title']]]);
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Campaign created in Mailchimp', ['campaign_id' => $data['id'], 'title' => $campaignData['title']]);
                return $data['id'];
            }
            Log::error('Failed to create campaign in Mailchimp', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating campaign in Mailchimp', ['error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Handle getCampaignAnalytics functionality with proper error handling.
     * @param string $campaignId
     * @return array|null
     */
    public function getCampaignAnalytics(string $campaignId): ?array
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])->get("{$this->baseUrl}/campaigns/{$campaignId}");
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting campaign analytics', ['campaign_id' => $campaignId, 'error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Handle bulkSyncSubscribers functionality with proper error handling.
     * @return array
     */
    public function bulkSyncSubscribers(): array
    {
        // Use LazyCollection with timeout to prevent long-running bulk sync operations
        $timeout = now()->addMinutes(10); // 10 minute timeout for bulk sync
        $subscribers = Subscriber::active()->cursor()->takeUntilTimeout($timeout)->collect();
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];
        foreach ($subscribers as $subscriber) {
            if ($this->syncSubscriberToMailchimp($subscriber)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = $subscriber->email;
            }
        }
        Log::info('Bulk sync completed', $results);
        return $results;
    }
    /**
     * Handle getMailchimpStats functionality with proper error handling.
     * @return array|null
     */
    public function getMailchimpStats(): ?array
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])->get("{$this->baseUrl}/lists/{$this->listId}");
            if ($response->successful()) {
                $data = $response->json();
                return ['total_subscribers' => $data['stats']['member_count'], 'unsubscribed' => $data['stats']['unsubscribe_count'], 'cleaned' => $data['stats']['cleaned_count'], 'pending' => $data['stats']['pending_count']];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting Mailchimp stats', ['error' => $e->getMessage()]);
            return null;
        }
    }
    /**
     * Handle mapStatusToMailchimp functionality with proper error handling.
     * @param string $status
     * @return string
     */
    private function mapStatusToMailchimp(string $status): string
    {
        return match ($status) {
            'active' => 'subscribed',
            'inactive' => 'unsubscribed',
            'unsubscribed' => 'unsubscribed',
            default => 'unsubscribed',
        };
    }
    /**
     * Handle createInterestSegment functionality with proper error handling.
     * @param string $interest
     * @return string|null
     */
    public function createInterestSegment(string $interest): ?string
    {
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'])->post("{$this->baseUrl}/lists/{$this->listId}/segments", ['name' => ucfirst($interest) . ' Subscribers', 'options' => ['match' => 'any', 'conditions' => [['condition_type' => 'Interests', 'field' => 'interests-' . $interest, 'op' => 'interestcontains', 'value' => [$interest]]]]]);
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Interest segment created in Mailchimp', ['segment_id' => $data['id'], 'interest' => $interest]);
                return $data['id'];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Exception creating interest segment', ['interest' => $interest, 'error' => $e->getMessage()]);
            return null;
        }
    }
}