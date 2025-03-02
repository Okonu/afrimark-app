<x-filament::page>
    <div class="space-y-6">
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeTab === 'disputable-listings'"
                wire:click="$set('activeTab', 'disputable-listings')"
                icon="heroicon-o-document-text"
                label="Disputable Listings"
            />

            <x-filament::tabs.item
                :active="$activeTab === 'my-disputes'"
                wire:click="$set('activeTab', 'my-disputes')"
                icon="heroicon-o-exclamation-triangle"
                label="My Disputes"
            />

            <x-filament::tabs.item
                :active="$activeTab === 'disputes-to-respond'"
                wire:click="$set('activeTab', 'disputes-to-respond')"
                icon="heroicon-o-chat-bubble-left"
                label="Disputes To Respond"
            />
        </x-filament::tabs>

        <div>
            @if ($activeTab === 'disputable-listings')
                <div class="mb-4">
                    <h2 class="text-xl font-bold">Disputable Listings</h2>
                    <p class="text-gray-500">
                        These are listings where your business has been identified as a debtor.
                        You can dispute these listings before they become publicly visible.
                    </p>
                </div>
                {{ $this->getDisputableListingsTable() }}
            @elseif ($activeTab === 'my-disputes')
                <div class="mb-4">
                    <h2 class="text-xl font-bold">My Disputes</h2>
                    <p class="text-gray-500">
                        Disputes you have raised against listings where your business has been identified as a debtor.
                    </p>
                </div>
                {{ $this->getMyDisputesTable() }}
            @elseif ($activeTab === 'disputes-to-respond')
                <div class="mb-4">
                    <h2 class="text-xl font-bold">Disputes To Respond</h2>
                    <p class="text-gray-500">
                        These are disputes raised by businesses you have listed as debtors.
                        Please respond to these disputes promptly.
                    </p>
                </div>
                {{ $this->getDisputesToRespondTable() }}
            @endif
        </div>
    </div>
</x-filament::page>
