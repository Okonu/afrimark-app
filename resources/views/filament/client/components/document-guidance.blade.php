{{--
    Document Guidance Component
    resources/views/filament/client/components/document-guidance.blade.php
--}}

<div class="bg-white rounded-xl border border-gray-300 p-6">
    <h3 class="text-lg font-medium text-gray-900">Document Upload Guidelines</h3>
    <div class="mt-4 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex space-x-3">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-10 w-10 rounded-md bg-primary-100 text-primary-600">
                        <x-heroicon-m-document-text class="h-6 w-6" />
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-medium text-gray-900">Business Documents</h4>
                    <p class="mt-1 text-sm text-gray-500">Upload your business registration certificate, KRA PIN document, CR12/CR13, and other official documentation. These are needed for verification.</p>
                </div>
            </div>

            <div class="flex space-x-3">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-10 w-10 rounded-md bg-primary-100 text-primary-600">
                        <x-heroicon-m-banknotes class="h-6 w-6" />
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-medium text-gray-900">Debtor Documents</h4>
                    <p class="mt-1 text-sm text-gray-500">Upload invoices, contracts, and other documents related to your debtors. These help establish the legitimacy of debts.</p>
                </div>
            </div>

            <div class="flex space-x-3">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-10 w-10 rounded-md bg-primary-100 text-primary-600">
                        <x-heroicon-m-exclamation-triangle class="h-6 w-6" />
                    </div>
                </div>
                <div>
                    <h4 class="text-base font-medium text-gray-900">Dispute Evidence</h4>
                    <p class="mt-1 text-sm text-gray-500">Documents submitted as evidence for disputes. These are added when you create or respond to disputes.</p>
                </div>
            </div>
        </div>
    </div>
</div>
