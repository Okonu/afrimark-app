<x-filament::page>
    <div x-data class="space-y-6">
        <!-- Tabs -->
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

        <!-- Disputable Listings Tab -->
        @if ($activeTab === 'disputable-listings')
            <div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
                <div class="filament-tables-header-container">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <h2 class="font-medium">Disputable Listings</h2>
                        <p class="text-sm text-gray-500">
                            Found {{ count($disputableListings) }} results
                        </p>
                    </div>
                </div>

                @if(count($disputableListings) > 0)
                    <div class="border-t">
                        <div class="overflow-y-auto">
                            <table class="w-full text-start">
                                <thead>
                                <tr class="bg-gray-50 border-y">
                                    <th class="w-12 px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        ID
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Debtor Name
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Listed By
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Amount Claimed
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Invoice Number
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute Window Closes
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($disputableListings as $listing)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 align-top w-12 whitespace-nowrap text-sm text-gray-600">
                                            {{ $listing->id }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $listing->name }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $listing->business->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatCurrency($listing->amount_owed) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $listing->invoice_number ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatDate($listing->listing_goes_live_at) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center justify-end gap-2">
                                                <a
                                                    href="{{ $this->getCreateDisputeUrl($listing->id) }}"
                                                    class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-danger-600 hover:bg-danger-500 focus:bg-danger-700 focus:ring-offset-danger-700"
                                                >
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Create Dispute</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-center text-gray-500">
                        <div class="mb-4 rounded-full bg-gray-100/80 p-3">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h2 class="mt-2 text-xl font-bold tracking-tight">No Disputable Listings</h2>
                        <p class="mt-1 text-sm">There are no pending listings against your business within the dispute window.</p>
                    </div>
                @endif
            </div>

            <!-- My Disputes Tab -->
        @elseif ($activeTab === 'my-disputes')
            <div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
                <div class="filament-tables-header-container">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <h2 class="font-medium">My Disputes</h2>
                        <p class="text-sm text-gray-500">
                            Found {{ count($myDisputes) }} results
                        </p>
                    </div>
                </div>

                @if(count($myDisputes) > 0)
                    <div class="border-t">
                        <div class="overflow-y-auto">
                            <table class="w-full text-start">
                                <thead>
                                <tr class="bg-gray-50 border-y">
                                    <th class="w-12 px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        ID
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Listed By
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Amount Claimed
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute Reason
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Status
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute Date
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($myDisputes as $dispute)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 align-top w-12 whitespace-nowrap text-sm text-gray-600">
                                            {{ $dispute->id }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $dispute->debtor->business->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatCurrency($dispute->debtor->amount_owed ?? 0) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatDisputeType($dispute->dispute_type) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            @php
                                                $statusColor = match($dispute->status) {
                                                    'pending' => 'warning',
                                                    'under_review' => 'primary',
                                                    'resolved_approved' => 'success',
                                                    'resolved_rejected' => 'danger',
                                                    default => 'secondary',
                                                };
                                                $statusClass = "filament-tables-badge-column flex gap-1 px-2 py-0.5 rounded-full text-xs font-medium tracking-tight bg-{$statusColor}-500/10 text-{$statusColor}-700";
                                                $statusLabel = ucfirst(str_replace('_', ' ', $dispute->status));
                                            @endphp
                                            <div class="{{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatDate($dispute->created_at) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center justify-end gap-2">
                                                <a
                                                    href="{{ $this->getViewDisputeUrl($dispute->id) }}"
                                                    class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2rem] px-3 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600"
                                                >
                                                    <span>View</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-center text-gray-500">
                        <div class="mb-4 rounded-full bg-gray-100/80 p-3">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h2 class="mt-2 text-xl font-bold tracking-tight">No Disputes Created</h2>
                        <p class="mt-1 text-sm">You have not created any disputes yet.</p>
                    </div>
                @endif
            </div>

            <!-- Disputes To Respond Tab -->
        @elseif ($activeTab === 'disputes-to-respond')
            <div class="filament-tables-container rounded-xl border border-gray-300 bg-white">
                <div class="filament-tables-header-container">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <h2 class="font-medium">Disputes To Respond</h2>
                        <p class="text-sm text-gray-500">
                            Found {{ count($disputesToRespond) }} results
                        </p>
                    </div>
                </div>

                @if(count($disputesToRespond) > 0)
                    <div class="border-t">
                        <div class="overflow-y-auto">
                            <table class="w-full text-start">
                                <thead>
                                <tr class="bg-gray-50 border-y">
                                    <th class="w-12 px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        ID
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Debtor Business
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Amount Claimed
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute Reason
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        Dispute Date
                                    </th>
                                    <th class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-600 text-start">
                                        &nbsp;
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($disputesToRespond as $dispute)
                                    <tr class="border-b">
                                        <td class="px-4 py-3 align-top w-12 whitespace-nowrap text-sm text-gray-600">
                                            {{ $dispute->id }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $dispute->debtor->name ?? 'Unknown' }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatCurrency($dispute->debtor->amount_owed ?? 0) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatDisputeType($dispute->dispute_type) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            {{ $this->formatDate($dispute->created_at) }}
                                        </td>
                                        <td class="px-4 py-3 align-top whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center justify-end gap-2">
                                                <a
                                                    href="{{ $this->getRespondDisputeUrl($dispute->id) }}"
                                                    class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2rem] px-3 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700"
                                                >
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Respond</span>
                                                </a>

                                                <a
                                                    href="{{ $this->getViewDisputeUrl($dispute->id) }}"
                                                    class="filament-button filament-button-size-sm inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2rem] px-3 text-sm text-gray-800 bg-white border-gray-300 hover:bg-gray-50 focus:ring-primary-600 focus:text-primary-600 focus:bg-primary-50 focus:border-primary-600"
                                                >
                                                    <span>View</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-center text-gray-500">
                        <div class="mb-4 rounded-full bg-gray-100/80 p-3">
                            <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <h2 class="mt-2 text-xl font-bold tracking-tight">No Disputes To Respond</h2>
                        <p class="mt-1 text-sm">There are no pending disputes that require your response.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament::page>
