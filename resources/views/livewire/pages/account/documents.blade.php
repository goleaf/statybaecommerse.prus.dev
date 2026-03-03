<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.documents') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.documents_description') }}</p>
    </header>

    @if (empty($documents))
        <p class="text-sm text-gray-500">{{ __('frontend.account.documents_empty') }}</p>
    @else
        <div class="overflow-hidden rounded border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('frontend.account.documents_table.title') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('frontend.account.documents_table.status') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('frontend.account.documents_table.format') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('frontend.account.documents_table.generated_at') }}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($documents as $doc)
                        <tr>
                            <td class="px-4 py-2">{{ $doc['title'] }}</td>
                            <td class="px-4 py-2">{{ ucfirst($doc['status'] ?? 'draft') }}</td>
                            <td class="px-4 py-2">{{ strtoupper($doc['format'] ?? 'html') }}</td>
                            <td class="px-4 py-2">{{ $doc['generated_at'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-right">
                                @if (!empty($doc['url']))
                                    <x-buttons.default :href="$doc['url']" target="_blank"
                                                       class="px-3 text-sm">{{ __('frontend.account.documents_table.download') }}</x-buttons.default>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
