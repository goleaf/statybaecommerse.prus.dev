@extends('layouts.app')

@section('title', __('discount_conditions.index.title'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('discount_conditions.index.title') }}</h1>
        <div class="flex space-x-4">
            <button onclick="testAllConditions()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                {{ __('discount_conditions.actions.test_all') }}
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">{{ __('discount_conditions.filters.title') }}</h2>
        <form method="GET" action="{{ route('discount-conditions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.type') }}</label>
                <select name="type" id="type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="discount_id" class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.discount') }}</label>
                <select name="discount_id" id="discount_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach($discounts as $discount)
                        <option value="{{ $discount->id }}" {{ request('discount_id') == $discount->id ? 'selected' : '' }}>{{ $discount->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="operator" class="block text-sm font-medium text-gray-700 mb-2">{{ __('discount_conditions.fields.operator') }}</label>
                <select name="operator" id="operator" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('common.all') }}</option>
                    @foreach($operators as $key => $label)
                        <option value="{{ $key }}" {{ request('operator') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('common.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('discount_conditions.stats.total_conditions') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $conditions->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('discount_conditions.stats.active_conditions') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $conditions->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('discount_conditions.stats.inactive_conditions') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $conditions->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('discount_conditions.stats.high_priority_conditions') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $conditions->where('priority', '>', 5)->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Conditions List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">{{ __('discount_conditions.list.title') }}</h2>
        </div>

        @if($conditions->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.discount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.operator') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.value') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.priority') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('discount_conditions.fields.condition') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($conditions as $condition)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $condition->discount->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $condition->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $condition->getOperatorLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if(is_array($condition->value))
                                        {{ implode(', ', $condition->value) }}
                                    @else
                                        {{ $condition->value }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $condition->priority > 5 ? 'bg-red-100 text-red-800' : ($condition->priority > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $condition->priority }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $condition->human_readable_condition }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('discount-conditions.show', $condition) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ __('common.view') }}
                                        </a>
                                        <button onclick="testCondition({{ $condition->id }})" class="text-green-600 hover:text-green-900">
                                            {{ __('discount_conditions.actions.test_condition') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $conditions->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('discount_conditions.empty.title') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('discount_conditions.empty.description') }}</p>
            </div>
        @endif
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
let currentConditionId = null;

function testCondition(conditionId) {
    currentConditionId = conditionId;
    document.getElementById('testModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
    document.getElementById('testForm').reset();
    currentConditionId = null;
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

function testAllConditions() {
    // Implementation for testing all conditions
    alert('Testing all conditions functionality would be implemented here.');
}
</script>
@endsection


