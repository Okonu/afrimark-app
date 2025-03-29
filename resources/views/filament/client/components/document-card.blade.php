{{--
    Document Card Component
    resources/views/filament/client/components/document-card.blade.php
--}}

@props(['document', 'type'])

<tr class="border-b hover:bg-gray-50">
    <td class="px-4 py-3 align-top w-12 whitespace-nowrap text-sm text-gray-600">
        {{ $document->id }}
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm font-medium text-gray-900">
        <div class="flex items-center">
            @php
                $icon = $this->getFileIcon($document->original_filename);
            @endphp
            <div class="flex-shrink-0 mr-2">
                <x-heroicon-o-{{ $icon }} class="h-5 w-5 text-gray-400" />
            </div>
            <div class="truncate max-w-[200px]" title="{{ $document->original_filename }}">
                {{ $document->original_filename }}
            </div>
        </div>
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        @if($type === 'business')
            {{ $document->type?->label() ?? $this->formatDocumentType($document->type) }}
        @elseif($type === 'dispute')
            Evidence Document
        @else
            {{ $this->formatDocumentType($document->type) }}
        @endif
    </td>

    @if($type === 'debtor')
        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
            {{ $document->debtor->name ?? 'Unknown' }}
        </td>
    @endif

    @if($type === 'dispute')
        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
            <a href="#" class="text-primary-600 hover:underline">
                {{ $document->dispute->id ?? 'Unknown' }}
            </a>
        </td>
    @endif

    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        {{ $this->formatDate($document->created_at) }}
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        @if($type === 'business' && $document->status)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $document->status->color() }}-100 text-{{ $document->status->color() }}-800">
                <x-heroicon-o-{{ $document->status->icon() }} class="w-4 h-4 mr-1" />
                {{ $document->status->label() }}
            </span>
        @elseif($type === 'debtor')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Uploaded
            </span>
        @elseif($type === 'dispute')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Submitted
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                Not Set
            </span>
        @endif
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        @php
            $status = $this->formatProcessingStatus($document->processing_status ?? 'pending');
        @endphp
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800">
            @if($document->processing_status === 'processing')
                <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-{{ $status['color'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            @elseif($document->processing_status === 'completed')
                <x-heroicon-o-check-circle class="w-4 h-4 mr-1 text-{{ $status['color'] }}-600" />
            @elseif($document->processing_status === 'failed')
                <x-heroicon-o-x-circle class="w-4 h-4 mr-1 text-{{ $status['color'] }}-600" />
            @elseif($document->processing_status === 'queued')
                <x-heroicon-o-clock class="w-4 h-4 mr-1 text-{{ $status['color'] }}-600" />
            @else
                <x-heroicon-o-document class="w-4 h-4 mr-1 text-{{ $status['color'] }}-600" />
            @endif
            {{ $status['label'] }}
        </span>
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        <div class="flex items-center justify-end gap-2">
            <x-filament::button
                size="sm"
                color="primary"
                wire:click="viewDocument({{ $document->id }}, '{{ $type }}')"
            >
                <x-slot name="icon">
                    <x-heroicon-m-eye class="w-4 h-4" />
                </x-slot>
                View
            </x-filament::button>

            <x-filament::button
                size="sm"
                color="success"
                wire:click="downloadDocument({{ $document->id }}, '{{ $type }}')"
            >
                <x-slot name="icon">
                    <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                </x-slot>
                Download
            </x-filament::button>

            @if($document->processing_status === 'failed' || !$document->isProcessed())
                <x-filament::button
                    size="sm"
                    color="danger"
                    wire:click="reprocessDocument({{ $document->id }}, '{{ $type }}')"
                >
                    <x-slot name="icon">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                    </x-slot>
                    {{ $document->processing_status === 'failed' ? 'Retry' : 'Process' }}
                </x-filament::button>
            @endif

            <x-filament::button
                size="sm"
                color="gray"
                wire:click="viewDocumentLogs({{ $document->id }}, '{{ $type }}')"
            >
                <x-slot name="icon">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                </x-slot>
                Logs
            </x-filament::button>
        </div>
    </td>
</tr>
