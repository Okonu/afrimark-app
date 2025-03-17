@props(['document', 'showExtractedData' => true])

<div class="space-y-5 p-4 bg-white rounded-lg border border-gray-200">
    <!-- Document info header -->
    <div class="flex justify-between items-start">
        <div class="flex items-start space-x-3">
            @php
                $extension = pathinfo($document->original_filename, PATHINFO_EXTENSION);
                $icon = match(strtolower($extension)) {
                    'pdf' => 'heroicon-o-document-text',
                    'doc', 'docx' => 'heroicon-o-document',
                    'xls', 'xlsx' => 'heroicon-o-table-cells',
                    default => 'heroicon-o-document',
                };
            @endphp

            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                    <x-dynamic-component :component="$icon" class="h-6 w-6 text-gray-500" />
                </div>
            </div>

            <div>
                <h3 class="text-base font-medium text-gray-900">{{ $document->original_filename }}</h3>
                <div class="mt-1 flex items-center">
                    <span class="text-sm text-gray-500">{{ $document->type instanceof \App\Enums\DocumentType ? $document->type->label() : ucwords(str_replace('_', ' ', $document->type)) }}</span>

                    @if($document->created_at)
                        <span class="text-gray-300 mx-2">|</span>
                        <span class="text-sm text-gray-500">Uploaded {{ $document->created_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($document->status))
            <div>
                @if($document->status instanceof \App\Enums\DocumentStatus)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $document->status->color() }}-100 text-{{ $document->status->color() }}-800">
                        <x-dynamic-component :component="$document->status->icon()" class="w-4 h-4 mr-1" />
                        {{ $document->status->label() }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ ucfirst($document->status) }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <!-- Document processing status -->
    @if(isset($document->processing_status))
        <div class="border-t border-gray-200 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Processing Status</p>
                    @php
                        $statusClass = match($document->processing_status ?? 'pending') {
                            'completed' => 'bg-success-100 text-success-800',
                            'failed' => 'bg-danger-100 text-danger-800',
                            'queued' => 'bg-primary-100 text-primary-800',
                            'processing' => 'bg-warning-100 text-warning-800',
                            default => 'bg-gray-100 text-gray-800',
                        };

                        $statusText = match($document->processing_status ?? 'pending') {
                            'completed' => 'Completed',
                            'failed' => 'Failed',
                            'queued' => 'Queued',
                            'processing' => 'Processing',
                            default => 'Pending',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </div>

                @if($document->processed_at)
                    <div>
                        <p class="text-sm font-medium text-gray-500">Processed</p>
                        <p class="text-sm text-gray-900">{{ $document->processed_at->format('M d, Y H:i') }}</p>
                    </div>
                @endif
            </div>

            @if($showExtractedData && method_exists($document, 'getExtractedData') && $document->isProcessed())
                @php
                    $extractedData = $document->getExtractedData();
                @endphp

                @if($extractedData)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-900 mb-2">Extracted Information</p>

                        <div class="bg-gray-50 rounded border border-gray-200 p-3 max-h-64 overflow-y-auto">
                            @if(is_array($extractedData))
                                <div class="space-y-2">
                                    @foreach($extractedData as $key => $value)
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
                            @elseif(is_string($extractedData))
                                <div class="text-sm text-gray-800 max-h-64 overflow-y-auto">
                                    {{ $extractedData }}
                                </div>
                            @else
                                <div class="text-sm text-gray-500 italic">
                                    Unable to display processing result. The data format is not supported.
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="mt-4 text-sm text-gray-500 italic">
                        No information was extracted from this document.
                    </div>
                @endif
            @endif
        </div>
    @endif

    <!-- Document actions -->
    @if($slot->isNotEmpty())
        <div class="border-t border-gray-200 pt-4">
            <div class="flex space-x-3">
                {{ $slot }}
            </div>
        </div>
    @endif
</div>
