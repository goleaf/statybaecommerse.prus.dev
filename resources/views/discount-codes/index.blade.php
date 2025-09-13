@extends('layouts.frontend')

@section('title', __('discount_codes'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('discount_codes') }}</h1>
            <p class="text-gray-600">{{ __('Find and apply discount codes to save on your purchases') }}</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        id="code-search" 
                        placeholder="{{ __('Enter discount code') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                <button 
                    id="validate-code" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    {{ __('discount_code_validate') }}
                </button>
            </div>
            
            <!-- Validation Result -->
            <div id="validation-result" class="mt-4 hidden"></div>
        </div>

        <!-- Available Codes -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Available Discount Codes') }}</h2>
            
            <div id="available-codes" class="space-y-4">
                <!-- Codes will be loaded here -->
            </div>
            
            <div id="loading-codes" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">{{ __('Loading discount codes...') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Discount Code Modal -->
<div id="code-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">{{ __('Discount Code Details') }}</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="modal-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const codeSearch = document.getElementById('code-search');
    const validateBtn = document.getElementById('validate-code');
    const validationResult = document.getElementById('validation-result');
    const availableCodes = document.getElementById('available-codes');
    const loadingCodes = document.getElementById('loading-codes');
    const codeModal = document.getElementById('code-modal');
    const modalContent = document.getElementById('modal-content');
    const closeModal = document.getElementById('close-modal');

    // Load available codes
    loadAvailableCodes();

    // Validate code
    validateBtn.addEventListener('click', function() {
        const code = codeSearch.value.trim();
        if (!code) {
            showValidationResult('error', '{{ __("Please enter a discount code") }}');
            return;
        }

        validateCode(code);
    });

    // Close modal
    closeModal.addEventListener('click', function() {
        codeModal.classList.add('hidden');
    });

    // Close modal on outside click
    codeModal.addEventListener('click', function(e) {
        if (e.target === codeModal) {
            codeModal.classList.add('hidden');
        }
    });

    function loadAvailableCodes() {
        fetch('/api/discount-codes/available')
            .then(response => response.json())
            .then(data => {
                loadingCodes.classList.add('hidden');
                displayAvailableCodes(data.codes);
            })
            .catch(error => {
                loadingCodes.classList.add('hidden');
                availableCodes.innerHTML = '<p class="text-red-600">{{ __("Failed to load discount codes") }}</p>';
            });
    }

    function displayAvailableCodes(codes) {
        if (codes.length === 0) {
            availableCodes.innerHTML = '<p class="text-gray-600 text-center py-8">{{ __("No discount codes available at the moment") }}</p>';
            return;
        }

        availableCodes.innerHTML = codes.map(code => `
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg text-gray-900">${code.code}</h3>
                        <p class="text-gray-600 mt-1">${code.description || ''}</p>
                        <div class="mt-2">
                            <span class="inline-block bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded">
                                ${code.discount.name}
                            </span>
                            <span class="inline-block bg-green-100 text-green-800 text-sm px-2 py-1 rounded ml-2">
                                ${code.discount.value}${code.discount.type === 'percentage' ? '%' : '€'} ${code.discount.type === 'percentage' ? '{{ __("off") }}' : '{{ __("off") }}'}
                            </span>
                        </div>
                        ${code.expires_at ? `<p class="text-sm text-gray-500 mt-2">{{ __("Expires") }}: ${code.expires_at}</p>` : ''}
                    </div>
                    <button 
                        onclick="showCodeDetails('${code.code}')"
                        class="ml-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        {{ __('View Details') }}
                    </button>
                </div>
            </div>
        `).join('');
    }

    function validateCode(code) {
        fetch('/api/discount-codes/validate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.valid) {
                showValidationResult('success', data.message);
                showCodeDetails(code);
            } else {
                showValidationResult('error', data.message);
            }
        })
        .catch(error => {
            showValidationResult('error', '{{ __("Something went wrong. Please try again.") }}');
        });
    }

    function showValidationResult(type, message) {
        validationResult.className = `mt-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
        validationResult.textContent = message;
        validationResult.classList.remove('hidden');
        
        setTimeout(() => {
            validationResult.classList.add('hidden');
        }, 5000);
    }

    window.showCodeDetails = function(code) {
        // Find code details from available codes
        const codeData = availableCodes.querySelector(`[onclick*="${code}"]`)?.closest('.border');
        if (codeData) {
            const codeText = codeData.querySelector('h3').textContent;
            const description = codeData.querySelector('p').textContent;
            
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ __('Code') }}:</h4>
                        <p class="text-2xl font-mono bg-gray-100 p-2 rounded">${codeText}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-900">{{ __('Description') }}:</h4>
                        <p class="text-gray-600">${description}</p>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            onclick="copyCode('${codeText}')"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            {{ __('discount_code_copy') }}
                        </button>
                        <button 
                            onclick="applyCode('${codeText}')"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            {{ __('discount_code_apply') }}
                        </button>
                    </div>
                </div>
            `;
            
            codeModal.classList.remove('hidden');
        }
    };

    window.copyCode = function(code) {
        navigator.clipboard.writeText(code).then(() => {
            showValidationResult('success', '{{ __("Code copied to clipboard") }}');
        });
    };

    window.applyCode = function(code) {
        fetch('/api/discount-codes/apply', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showValidationResult('success', data.message);
                codeModal.classList.add('hidden');
            } else {
                showValidationResult('error', data.message);
            }
        })
        .catch(error => {
            showValidationResult('error', '{{ __("Something went wrong. Please try again.") }}');
        });
    };
});
</script>
@endpush
@endsection

