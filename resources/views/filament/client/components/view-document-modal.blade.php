{{--
    View Document Modal Component
    resources/views/filament/client/components/view-document-modal.blade.php
--}}

<div>
    <x-filament::modal
        id="view-document-modal"
        wire:model.defer="isViewModalOpen"
        width="lg"
    >
        <x-slot name="heading">
            @if($selectedDocument)
                Document Details: {{ $selectedDocument->original_filename }}
            @else
                Document Details
            @endif
        </x-slot>

        @if($selectedDocument)
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
                                        <x-dynamic-component :component="$selectedDocument->status->icon()" class="w-4 h-4 mr-1" />
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

                <!-- Processing information -->
                <div>
                    <h3 class="text-base font-medium text-gray-900 mb-3">Document Processing</h3>

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

                        @if($processingResult)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-900 mb-2">Extracted Information</p>

                                <div class="bg-white rounded border border-gray-200 p-3">
                                    @if(is_array($processingResult))
                                        <div class="space-y-2">
                                            @foreach($processingResult as $key => $value)
                                                <div>
                                                    <span class="text-xs font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                    @if(is_array($value))
                                                        <pre class="text-xs mt-1 bg-gray-50 p-2 rounded overflow-auto max-h-20">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                                    @else
                                                        <span class="text-xs">{{ is_string($value) ? $value : json_encode($value) }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif(is_string($processingResult))
                                        <div class="text-sm text-gray-800 max-h-96 overflow-y-auto">
                                            {{ $processingResult }}
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500 italic">
                                            Unable to display processing result. The data format is not supported.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 italic">
                                @if($selectedDocument->isProcessed())
                                    No information was extracted from this document.
                                @elseif($selectedDocument->processing_status === 'failed')
                                    Processing failed. You can try reprocessing the document.
                                @elseif($selectedDocument->processing_status === 'queued')
                                    Document is queued for processing. This might take a few minutes.
                                @else
                                    Document has not been processed yet.
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Document actions -->
                @if(isset($selectedDocument->file_path))
                    <div>
                        <h3 class="text-base font-medium text-gray-900 mb-3">Document Actions</h3>
                        <div class="flex space-x-3">
                            <x-filament::button
                                color="success"
                                wire:click="downloadDocument({{ $selectedDocument->id }}, '{{ $activeTab === 'business-documents' ? 'business' : ($activeTab === 'debtor-documents' ? 'debtor' : 'dispute') }}')"
                            >
                                <x-slot name="icon">
                                    <x-heroicon-m-arrow-down-tray class="w-5 h-5" />
                                </x-slot>
                                Download Document
                            </x-filament::button>

                            @if($selectedDocument->processing_status === 'failed')
                                <x-filament::button
                                    color="danger"
                                    wire:click="reprocessDocument({{ $selectedDocument->id }}, '{{ $activeTab === 'business-documents' ? 'business' : ($activeTab === 'debtor-documents' ? 'debtor' : 'dispute') }}')"
                                >
                                    <x-slot name="icon">
                                        <x-heroicon-m-arrow-path class="w-5 h-5" />
                                    </x-slot>
                                    Retry Processing
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endif
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
