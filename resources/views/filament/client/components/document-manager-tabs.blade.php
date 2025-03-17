{{--
    Custom Tabs Component with Visible Labels
    resources/views/filament/client/components/document-manager-tabs.blade.php
--}}

<div class="filament-tabs flex overflow-x-auto items-center gap-x-1 shrink-0">
    <button
        type="button"
        class="flex items-center gap-x-2 shrink-0 px-3 py-2 rounded-lg text-sm font-medium filament-tabs-item {{ $activeTab === 'business-documents' ? 'bg-primary-500 text-white' : 'hover:bg-gray-500/5 focus:bg-gray-500/5 text-gray-700' }}"
        wire:click="$set('activeTab', 'business-documents')"
        title="View and manage your business registration documents"
    >
        <x-heroicon-o-building-office class="h-5 w-5" />
        <span>Business Documents</span>
    </button>

    <button
        type="button"
        class="flex items-center gap-x-2 shrink-0 px-3 py-2 rounded-lg text-sm font-medium filament-tabs-item {{ $activeTab === 'debtor-documents' ? 'bg-primary-500 text-white' : 'hover:bg-gray-500/5 focus:bg-gray-500/5 text-gray-700' }}"
        wire:click="$set('activeTab', 'debtor-documents')"
        title="View and manage invoices and contracts with debtors"
    >
        <x-heroicon-o-banknotes class="h-5 w-5" />
        <span>Debtor Documents</span>
    </button>

    <button
        type="button"
        class="flex items-center gap-x-2 shrink-0 px-3 py-2 rounded-lg text-sm font-medium filament-tabs-item {{ $activeTab === 'dispute-documents' ? 'bg-primary-500 text-white' : 'hover:bg-gray-500/5 focus:bg-gray-500/5 text-gray-700' }}"
        wire:click="$set('activeTab', 'dispute-documents')"
        title="View documents submitted as evidence for disputes"
    >
        <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
        <span>Dispute Evidence</span>
    </button>
</div>
