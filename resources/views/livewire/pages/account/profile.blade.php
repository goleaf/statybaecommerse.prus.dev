{{-- Account Profile Component --}}

<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.profile') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.navigation.profile_description') }}</p>
    </header>

    <div class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ __('frontend.profile.account_details.title') }}</h2>
                <livewire:components.profile.update-profile-information-form />
            </div>
            
            <div class="rounded-lg border border-gray-200 p-6">
                <h2 class="mb-4 text-base font-semibold text-gray-900">{{ __('ui.update_password') }}</h2>
                <livewire:components.profile.update-password-form />
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 p-6">
            <h2 class="mb-4 text-base font-semibold text-gray-900">{{ __('frontend.account.navigation.documents') }}</h2>

            @if ($recentInvoices->isEmpty())
                <p class="text-sm text-gray-500">{{ __('frontend.account.documents_empty') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentInvoices as $invoice)
                        @php
                            $downloadUrl = $invoice->status === \App\Models\OrderInvoice::STATUS_READY ? $invoice->downloadUrl() : null;
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded border border-gray-200 p-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $invoice->full_number ?: ($invoice->external_invoice_id ?: 'Invoice') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Order {{ $invoice->order?->number }}
                                    · {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $invoice->status)) }}
                                </p>
                            </div>
                            @if (is_string($downloadUrl) && $downloadUrl !== '')
                                <a href="{{ $downloadUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-blue-600 hover:underline">
                                    Download PDF
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <div class="rounded-lg p-6 border border-red-200">
            <h2 class="mb-4 text-base font-semibold text-red-700">{{ __('ui.delete_account') }}</h2>
            <livewire:components.profile.delete-user-form />
        </div>
    </div>
</div>
