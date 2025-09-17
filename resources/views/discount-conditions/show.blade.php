@extends('layouts.app')

@section('title', $discountCondition->translated_name ?? __('discount_conditions.show.title'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $discountCondition->translated_name ?? __('discount_conditions.show.title') }}</h1>
            <p class="text-gray-600 mt-2">{{ $discountCondition->translated_description ?? __('discount_conditions.show.subtitle') }}</p>
        </div>
        <div class="flex space-x-4">
            <button onclick="testCondition({{ $discountCondition->id }})" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                {{ __('discount_conditions.actions.test_condition') }}
            </button>
            <a href="{{ route('discount-conditions.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                {{ __('common.back') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-6">{{ __('discount_conditions.sections.basic_info') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.discount') }}</label>
                        <div class="text-lg font-medium text-gray-900">
                            <a href="{{ route('discounts.show', $discountCondition->discount) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $discountCondition->discount->name }}
                            </a>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.type') }}</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $discountCondition->getTypeLabel() }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.operator') }}</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            {{ $discountCondition->getOperatorLabel() }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.value') }}</label>
                        <div class="text-lg font-medium text-gray-900">
                            @if(is_array($discountCondition->value))
                                <div class="flex flex-wrap gap-1">
                                    @foreach($discountCondition->value as $value)
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $value }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                {{ $discountCondition->value }}
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.priority') }}</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            {{ $discountCondition->priority > 5 ? 'bg-red-100 text-red-800' : ($discountCondition->priority > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $discountCondition->priority }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.position') }}</label>
                        <div class="text-lg font-medium text-gray-900">{{ $discountCondition->position }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.is_active') }}</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            {{ $discountCondition->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $discountCondition->is_active ? __('common.yes') : __('common.no') }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.created_at') }}</label>
                        <div class="text-lg font-medium text-gray-900">{{ $discountCondition->created_at->format('Y-m-d H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <!-- Condition Description -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.fields.condition') }}</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-lg font-medium text-gray-900">{{ $discountCondition->human_readable_condition }}</p>
                </div>
            </div>

            <!-- Metadata -->
            @if($discountCondition->metadata)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.fields.metadata') }}</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-sm text-gray-700 whitespace-pre-wrap">{{ json_encode($discountCondition->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Translations -->
            @if($discountCondition->translations->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.sections.translations') }}</h2>
                    <div class="space-y-4">
                        @foreach($discountCondition->translations as $translation)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ strtoupper($translation->locale) }}</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $translation->locale }}
                                    </span>
                                </div>
                                @if($translation->name)
                                    <p class="text-sm text-gray-900 font-medium">{{ $translation->name }}</p>
                                @endif
                                @if($translation->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $translation->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.quick_actions.title') }}</h2>
                <div class="space-y-3">
                    <button onclick="testCondition({{ $discountCondition->id }})" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        {{ __('discount_conditions.actions.test_condition') }}
                    </button>
                    <a href="{{ route('discounts.show', $discountCondition->discount) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center block">
                        {{ __('discount_conditions.actions.view_discount') }}
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.stats.title') }}</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('discount_conditions.stats.priority') }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $discountCondition->priority }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('discount_conditions.stats.position') }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $discountCondition->position }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">{{ __('discount_conditions.stats.status') }}</span>
                        <span class="text-sm font-medium {{ $discountCondition->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $discountCondition->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Condition Modal -->
<div id="testModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('discount_conditions.test.title') }}</h3>
            <form id="testForm">
                <div class="mb-4">
                    <label for="testValue" class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.test_value') }}</label>
                    <input type="text" id="testValue" name="test_value" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-sm text-gray-500 mt-1">{{ __('discount_conditions.helpers.test_value') }}</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeTestModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('discount_conditions.actions.test_condition') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentConditionId = {{ $discountCondition->id }};

function testCondition(conditionId) {
    currentConditionId = conditionId;
    document.getElementById('testModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
    document.getElementById('testForm').reset();
}

document.getElementById('testForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const testValue = document.getElementById('testValue').value;
    
    fetch(`/discount-conditions/${currentConditionId}/test`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ test_value: testValue })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        closeTestModal();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while testing the condition.');
    });
});
</script>
@endsection

