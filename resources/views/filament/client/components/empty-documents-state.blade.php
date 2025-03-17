{{--
    Empty Documents State Component
    resources/views/filament/client/components/empty-documents-state.blade.php
--}}

@props(['activeTab'])

<div class="flex flex-col items-center justify-center px-6 py-12 text-center text-gray-500">
    <div class="mb-4 rounded-full bg-gray-100/80 p-3">
        <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
    </div>
    <h2 class="mt-2 text-xl font-bold tracking-tight">No Documents Found</h2>
    @if($activeTab === 'business-documents')
        <p class="mt-1 text-sm">You haven't uploaded any business documents yet. Upload documents like Certificate of Incorporation, KRA PIN, or CR12/CR13.</p>
    @elseif($activeTab === 'debtor-documents')
        <p class="mt-1 text-sm">No debtor documents found. Upload invoices, contracts, or other debtor-related documents.</p>
    @elseif($activeTab === 'dispute-documents')
        <p class="mt-1 text-sm">No dispute evidence documents found. Documents are added when you create or respond to disputes.</p>
    @endif

    @if($activeTab !== 'dispute-documents')
        <div class="mt-6">
            <x-filament::button wire:click="openUploadModal" color="primary">
                <x-slot name="icon">
                    <x-heroicon-m-arrow-up-tray class="w-5 h-5" />
                </x-slot>
                Upload New Document
            </x-filament::button>
        </div>
    @endif
</div>
