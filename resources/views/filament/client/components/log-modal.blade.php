<x-filament::modal
    id="document-log-modal"
    wire:model.defer="isLogModalOpen"
    width="xl"
>
    <x-slot name="heading">
        @if($selectedDocument)
            Processing Logs: {{ $selectedDocument->original_filename }}
        @else
            Document Logs
        @endif
    </x-slot>

    @if($selectedDocument && !empty($documentLogs))
        <div class="space-y-2">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Document Processing Timeline</h3>

                <div class="space-y-3">
                    @foreach($documentLogs as $log)
                        <div class="flex">
                            <div class="flex-shrink-0">
                                @if($log['status'] === 'info')
                                    <span class="h-4 w-4 rounded-full bg-blue-400 flex items-center justify-center">
                                        <span class="h-2 w-2 bg-white rounded-full"></span>
                                    </span>
                                @elseif($log['status'] === 'success')
                                    <span class="h-4 w-4 rounded-full bg-green-400 flex items-center justify-center">
                                        <span class="h-2 w-2 bg-white rounded-full"></span>
                                    </span>
                                @elseif($log['status'] === 'warning')
                                    <span class="h-4 w-4 rounded-full bg-yellow-400 flex items-center justify-center">
                                        <span class="h-2 w-2 bg-white rounded-full"></span>
                                    </span>
                                @elseif($log['status'] === 'danger')
                                    <span class="h-4 w-4 rounded-full bg-red-400 flex items-center justify-center">
                                        <span class="h-2 w-2 bg-white rounded-full"></span>
                                    </span>
                                @endif
                            </div>
                            <div class="ml-3">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">{{ $log['message'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $log['time'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Processing Details</h3>

                <div class="space-y-2">
                    <div class="flex justify-between">
                        <p class="text-xs font-medium text-gray-500">Current Status</p>
                        <p class="text-xs text-gray-900">
                            @php
                                $status = $this->formatProcessingStatus($selectedDocument->processing_status ?? 'pending');
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $status['color'] }}-100 text-{{ $status['color'] }}-800">
                                {{ $status['label'] }}
                            </span>
                        </p>
                    </div>

                    @if($selectedDocument->processed_at)
                        <div class="flex justify-between">
                            <p class="text-xs font-medium text-gray-500">Processed At</p>
                            <p class="text-xs text-gray-900">{{ $selectedDocument->processed_at }}</p>
                        </div>
                    @endif

                    @if(isset($selectedDocument->processing_result['status_code']))
                        <div class="flex justify-between">
                            <p class="text-xs font-medium text-gray-500">Response Status Code</p>
                            <p class="text-xs text-gray-900">{{ $selectedDocument->processing_result['status_code'] }}</p>
                        </div>
                    @endif

                    @if(isset($selectedDocument->processing_result['request_id']))
                        <div class="flex justify-between">
                            <p class="text-xs font-medium text-gray-500">Request ID</p>
                            <p class="text-xs text-gray-900">{{ $selectedDocument->processing_result['request_id'] }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="text-gray-500 text-center py-4">
            No processing logs available for this document.
        </div>
    @endif

    <x-slot name="footer">
        <div class="flex justify-end">
            <x-filament::button wire:click="$set('isLogModalOpen', false)" color="gray">
                Close
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>
