<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Email Marketing Manager</h2>
                <p class="text-gray-600 mt-1">Manage your email campaigns and subscriber sync with Mailchimp</p>
            </div>
            <button 
                wire:click="refreshStats"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh Stats
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Mailchimp Statistics --}}
    @if($showStats && !empty($mailchimpStats))
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mailchimp Statistics</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($mailchimpStats['total_subscribers'] ?? 0) }}</div>
                    <div class="text-sm text-blue-800">Total Subscribers</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($mailchimpStats['pending'] ?? 0) }}</div>
                    <div class="text-sm text-green-800">Pending</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-yellow-600">{{ number_format($mailchimpStats['unsubscribed'] ?? 0) }}</div>
                    <div class="text-sm text-yellow-800">Unsubscribed</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($mailchimpStats['cleaned'] ?? 0) }}</div>
                    <div class="text-sm text-red-800">Cleaned</div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Sync Subscribers --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sync Subscribers</h3>
            <p class="text-gray-600 mb-4">Sync all active subscribers to Mailchimp</p>
            
            @if(!empty($syncResults))
                <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Last Sync Results:</h4>
                    <div class="text-sm text-gray-600">
                        <div>✅ Successful: {{ $syncResults['success'] }}</div>
                        <div>❌ Failed: {{ $syncResults['failed'] }}</div>
                        @if(!empty($syncResults['errors']))
                            <div class="mt-2">
                                <strong>Failed emails:</strong>
                                <div class="text-xs text-red-600 mt-1">
                                    {{ implode(', ', array_slice($syncResults['errors'], 0, 5)) }}
                                    @if(count($syncResults['errors']) > 5)
                                        and {{ count($syncResults['errors']) - 5 }} more...
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <button 
                wire:click="syncAllSubscribers"
                wire:loading.attr="disabled"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <span wire:loading.remove wire:target="syncAllSubscribers">Sync All Subscribers</span>
                <span wire:loading wire:target="syncAllSubscribers" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Syncing...
                </span>
            </button>
        </div>

        {{-- Create Campaign --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Campaign</h3>
            
            <form wire:submit.prevent="createCampaign" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campaign Title</label>
                    <input 
                        type="text" 
                        wire:model="campaignTitle"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter campaign title"
                    >
                    @error('campaignTitle') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Line</label>
                    <input 
                        type="text" 
                        wire:model="campaignSubject"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter subject line"
                    >
                    @error('campaignSubject') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                        <input 
                            type="text" 
                            wire:model="fromName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('fromName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reply To</label>
                        <input 
                            type="email" 
                            wire:model="replyTo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        @error('replyTo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Interest (Optional)</label>
                    <select 
                        wire:model="selectedInterest"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Subscribers</option>
                        @foreach($this->interests as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
                >
                    Create Campaign
                </button>
            </form>
        </div>
    </div>

    {{-- Interest Segments --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Interest Segments</h3>
        <p class="text-gray-600 mb-4">Create targeted segments based on subscriber interests</p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($this->interests as $value => $label)
                <button 
                    wire:click="createInterestSegment('{{ $value }}')"
                    class="p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    <div class="font-medium text-gray-900">{{ $label }}</div>
                    <div class="text-sm text-gray-500">{{ $value }}</div>
                </button>
            @endforeach
        </div>
    </div>
</div>
