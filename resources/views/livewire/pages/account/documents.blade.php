<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.templates.account')] class extends Component {
    public array $documents = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->documents = $user
                ->documents()
                ->latest('generated_at')
                ->limit(200)
                ->get(['id', 'title', 'format', 'file_path', 'status', 'generated_at'])
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'format' => $doc->format,
                        'status' => $doc->status,
                        'generated_at' => optional($doc->generated_at)->toDateTimeString(),
                        'url' => $doc->getFileUrl(),
                    ];
                })
                ->toArray();
        }
    }
}; ?>

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('My account'), 'url' => route('account.index')], ['label' => __('Documents')]]" />
    <x-page-heading :title="__('Documents')" :description="__('Invoices and generated documents')" />

    @if (empty($documents))
        <p class="text-sm text-gray-500">{{ __('No documents yet.') }}</p>
    @else
        <div class="overflow-hidden rounded border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Title') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Status') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Format') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            {{ __('Generated at') }}</th>
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
                                                       class="px-3 text-sm">{{ __('Download') }}</x-buttons.default>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
