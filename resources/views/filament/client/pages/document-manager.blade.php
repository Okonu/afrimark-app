<x-filament::page>
    <div class="space-y-6">
        <!-- Header with action buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
            <div>
                <h2 class="text-xl font-bold text-gray-900">
                    Document Management
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Upload, view, and manage all your business-related documents in one place
                </p>
            </div>

            <x-filament::button wire:click="openUploadModal" color="primary">
                <x-slot name="icon">
                    <x-heroicon-m-arrow-up-tray class="w-5 h-5" />
                </x-slot>
                Upload New Document
            </x-filament::button>
        </div>

        <!-- Info Alert -->
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <x-heroicon-m-information-circle class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Document Processing Information</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>When you upload a document, our system will automatically process it to extract relevant information. This may take a few minutes depending on the document size and complexity. Once processing is complete, you'll be able to view the extracted data and details.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Tabs with Visible Labels -->
        @include('filament.client.components.document-manager-tabs')

        <!-- Document List Container -->
        <div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
            <div class="filament-tables-header-container">
                <div class="px-4 py-3 flex items-center justify-between">
                    <h2 class="font-medium">
                        @if($activeTab === 'business-documents')
                            Business Documents
                            <span class="text-xs text-gray-500 ml-2">
                                (Certificate of Incorporation, KRA PIN, CR12/CR13, and other business documents)
                            </span>
                        @elseif($activeTab === 'debtor-documents')
                            Debtor Documents
                            <span class="text-xs text-gray-500 ml-2">
                                (Invoices, contracts, and other debtor-related documents)
                            </span>
                        @elseif($activeTab === 'dispute-documents')
                            Dispute Evidence
                            <span class="text-xs text-gray-500 ml-2">
                                (Documents submitted as evidence for disputes)
                            </span>
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500">
                        Found {{ $this->getDocumentCount() }} results
                    </p>
                </div>
            </div>

            @if($this->hasDocuments())
                <div class="border-t">
                    <div class="overflow-y-auto">
                        <table class="w-full text-start">
                            <thead>
                            <tr class="bg-gray-50 border-y">
                                <th class="w-12 px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    ID
                                </th>
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    Document Name
                                </th>
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    Type
                                </th>
                                @if($activeTab === 'debtor-documents')
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Debtor
                                    </th>
                                @endif
                                @if($activeTab === 'dispute-documents')
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute ID
                                    </th>
                                @endif
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    Upload Date
                                </th>
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    Status
                                </th>
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    Processing
                                </th>
                                <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                    &nbsp;
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($activeTab === 'business-documents')
                                @foreach($businessDocuments as $document)
                                    @include('filament.client.components.document-card', ['document' => $document, 'type' => 'business'])
                                @endforeach
                            @elseif($activeTab === 'debtor-documents')
                                @foreach($debtorDocuments as $document)
                                    @include('filament.client.components.document-card', ['document' => $document, 'type' => 'debtor'])
                                @endforeach
                            @elseif($activeTab === 'dispute-documents')
                                @foreach($disputeDocuments as $document)
                                    @include('filament.client.components.document-card', ['document' => $document, 'type' => 'dispute'])
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                @include('filament.client.components.empty-documents-state', ['activeTab' => $activeTab])
            @endif
        </div>

        <!-- Guidance section -->
        @include('filament.client.components.document-guidance')

        <!-- Modals -->
        @include('filament.client.components.upload-document-modal')
        @include('filament.client.components.view-document-modal')
    </div>
</x-filament::page>
