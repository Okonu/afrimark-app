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
                <x-dynamic-component :component="$icon" class="h-5 w-5 text-gray-400" />
            </div>
            <div>
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
                <x-dynamic-component :component="$document->status->icon()" class="w-4 h-4 mr-1" />
                {{ $document->status->label() }}
            </span>
        @elseif($type === 'debtor')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800">
                Uploaded
            </span>
        @elseif($type === 'dispute')
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-info-100 text-info-800">
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
            {{ $status['label'] }}
        </span>
    </td>
    <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
        <div class="flex items-center justify-end gap-2">
            <x-filament::button
                size="sm"
                color="gray"
                wire:click="viewDocument({{ $document->id }}, '{{ $type }}')"
            >
                View Details
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

            @if($document->processing_status === 'failed')
                <x-filament::button
                    size="sm"
                    color="danger"
                    wire:click="reprocessDocument({{ $document->id }}, '{{ $type }}')"
                >
                    Retry Processing
                </x-filament::button>
            @endif
        </div>
    </td>
</tr>
