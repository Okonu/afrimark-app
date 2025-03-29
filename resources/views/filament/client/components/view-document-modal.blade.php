{{--
    View Document Modal Component
    resources/views/filament/client/components/view-document-modal.blade.php
--}}

<div>
    <x-filament::modal
        id="view-document-modal"
        wire:model.defer="isViewModalOpen"
        width="2xl" {{-- Increased width for better display of analysis data --}}
    >
        <x-slot name="heading">
            @if($selectedDocument)
                Document Details: {{ $selectedDocument->original_filename }}
            @else
                Document Details
            @endif
        </x-slot>

        @if($selectedDocument)
            @php
                // Determine document type based on class
                if ($selectedDocument instanceof \App\Models\BusinessDocument) {
                    $documentType = 'business';
                } elseif ($selectedDocument instanceof \App\Models\DebtorDocument) {
                    $documentType = 'debtor';
                } elseif ($selectedDocument instanceof \App\Models\DisputeDocument) {
                    $documentType = 'dispute';
                } else {
                    $documentType = 'unknown';
                }
            @endphp

            <div class="space-y-6">
                <!-- Document information -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Document Name</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $selectedDocument->original_filename }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Document Type</p>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($selectedDocument instanceof \App\Models\BusinessDocument && $selectedDocument->type)
                                    {{ $selectedDocument->type->label() }}
                                @else
                                    {{ $this->formatDocumentType($selectedDocument->type ?? 'unknown') }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Upload Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $this->formatDate($selectedDocument->created_at) }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1 text-sm">
                                @if($selectedDocument instanceof \App\Models\BusinessDocument && $selectedDocument->status)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $selectedDocument->status->color() }}-100 text-{{ $selectedDocument->status->color() }}-800">
                                        <x-heroicon-o-{{ $selectedDocument->status->icon() }} class="w-4 h-4 mr-1" />
                                        {{ $selectedDocument->status->label() }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Uploaded
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabs for Document Preview and Analysis -->
                <div x-data="{ activeTab: 'analysis' }">
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'preview'"
                                    class="py-2 px-1 font-medium text-sm"
                                    :class="activeTab === 'preview' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Document Preview
                            </button>
                            <button @click="activeTab = 'analysis'"
                                    class="py-2 px-1 font-medium text-sm"
                                    :class="activeTab === 'analysis' ? 'border-b-2 border-primary-500 text-primary-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                Document Analysis
                            </button>
                        </nav>
                    </div>

                    <!-- Document Preview Tab -->
                    <div x-show="activeTab === 'preview'">
                        @if(strtolower(pathinfo($selectedDocument->file_path, PATHINFO_EXTENSION)) === 'pdf')
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="w-full h-96 bg-white rounded border overflow-hidden">
                                    @if(Storage::exists($selectedDocument->file_path))
                                        <iframe src="{{ Storage::url($selectedDocument->file_path) }}" class="w-full h-full"></iframe>
                                    @else
                                        <div class="flex items-center justify-center h-full text-gray-500">
                                            Document file not found
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="flex flex-col items-center justify-center p-6">
                                    @php
                                        $icon = $this->getFileIcon($selectedDocument->original_filename);
                                    @endphp
                                    <x-heroicon-o-{{ $icon }} class="w-16 h-16 text-gray-400" />
                                    <p class="mt-2 text-sm text-gray-600">{{ $selectedDocument->original_filename }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Document Analysis Tab -->
                    <div x-show="activeTab === 'analysis'">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Processing Status</p>
                                    @php
                                        $status = $this->formatProcessingStatus($selectedDocument->processing_status ?? 'pending');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800">
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                @if($selectedDocument->processed_at)
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">Processed On</p>
                                        <p class="text-sm text-gray-500">{{ $this->formatDate($selectedDocument->processed_at) }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($selectedDocument->processing_status === 'processing')
                                <div class="text-center py-8">
                                    <svg class="animate-spin mx-auto h-8 w-8 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <p class="mt-3 text-sm text-gray-600">Document analysis in progress...</p>
                                </div>
                            @elseif($selectedDocument->processing_status === 'queued')
                                <div class="text-center py-8">
                                    <x-heroicon-o-clock class="mx-auto h-8 w-8 text-primary-500" />
                                    <p class="mt-3 text-sm text-gray-600">Document queued for analysis. This process may take a few minutes.</p>
                                </div>
                            @elseif($selectedDocument->processing_status === 'failed')
                                <div class="bg-red-50 p-4 rounded-lg mb-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <x-heroicon-o-exclamation-circle class="h-5 w-5 text-red-400" />
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-red-800">Processing Failed</h3>
                                            <div class="mt-2 text-sm text-red-700">
                                                @if($processingResult && isset($processingResult['error']))
                                                    <p>{{ $processingResult['error'] }}</p>
                                                @else
                                                    <p>An error occurred during document processing. Please try again.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($selectedDocument->processing_status === 'completed')
                                @if($selectedDocument->isProcessingSuccessful())
                                    @php
                                        $extractedData = $selectedDocument->getExtractedData();
                                    @endphp

                                    @if($extractedData)
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-900 mb-2">Extracted Information</p>

                                            <div class="bg-white rounded border border-gray-200 overflow-hidden">
                                                @if(is_array($extractedData))
                                                    <div class="divide-y divide-gray-200">
                                                        @foreach($extractedData as $key => $value)
                                                            <div class="p-3">
                                                                <dt class="text-sm font-medium text-gray-500">
                                                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                                                </dt>
                                                                <dd class="mt-1">
                                                                    @if(is_array($value))
                                                                        <pre class="text-xs mt-1 bg-gray-50 p-2 rounded overflow-auto max-h-48">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                                    @else
                                                                        <span class="text-sm text-gray-900">{{ $value }}</span>
                                                                    @endif
                                                                </dd>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @elseif(is_string($extractedData))
                                                    <div class="p-4 text-sm text-gray-800 whitespace-pre-line max-h-96 overflow-y-auto">
                                                        {{ $extractedData }}
                                                    </div>
                                                @else
                                                    <div class="p-4 text-sm text-gray-500 italic">
                                                        Unable to display extracted data in a readable format.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-4 bg-yellow-50 rounded-lg text-sm text-yellow-700">
                                            The document was processed successfully but no relevant information could be extracted.
                                        </div>
                                    @endif
                                @else
                                    <div class="p-4 bg-yellow-50 rounded-lg text-sm text-yellow-700">
                                        Document processing completed but with issues. Some information may be missing or incomplete.
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <x-heroicon-o-document-text class="mx-auto h-8 w-8 text-gray-400" />
                                    <p class="mt-3 text-sm text-gray-600">This document has not been processed yet.</p>
                                    <x-filament::button
                                        wire:click="reprocessDocument({{ $selectedDocument->id }}, '{{ $documentType }}')"
                                        color="primary"
                                        class="mt-4"
                                    >
                                        Process Document Now
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Document actions -->
                <div>
                    <h3 class="text-base font-medium text-gray-900 mb-3">Document Actions</h3>
                    <div class="flex space-x-3">
                        <x-filament::button
                            color="success"
                            wire:click="downloadDocument({{ $selectedDocument->id }}, '{{ $documentType }}')"
                        >
                            <x-slot name="icon">
                                <x-heroicon-m-arrow-down-tray class="w-5 h-5" />
                            </x-slot>
                            Download Document
                        </x-filament::button>

                        @if($selectedDocument->processing_status === 'failed' || !$selectedDocument->isProcessed())
                            <x-filament::button
                                color="primary"
                                wire:click="reprocessDocument({{ $selectedDocument->id }}, '{{ $documentType }}')"
                            >
                                <x-slot name="icon">
                                    <x-heroicon-m-arrow-path class="w-5 h-5" />
                                </x-slot>
                                {{ $selectedDocument->processing_status === 'failed' ? 'Retry Processing' : 'Process Document' }}
                            </x-filament::button>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-gray-500 text-center py-4">
                No document selected or document could not be found.
            </div>
        @endif

        <x-slot name="footer">
            <div class="flex justify-end">
                <x-filament::button wire:click="closeViewModal" color="gray">
                    Close
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
