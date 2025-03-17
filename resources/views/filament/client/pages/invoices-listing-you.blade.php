<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Invoices Listing Your Business</h2>
                    @if($this->businessId)
                        @php
                            $businessName = \App\Models\Business::find($this->businessId)?->name ?? 'Unknown Business';
                        @endphp
                        <div class="px-3 py-1 bg-primary-100 text-primary-700 rounded-full">
                            Filtering by: {{ $businessName }}
                            <a href="{{ route('filament.client.pages.invoices-listing-you') }}" class="ml-2 text-primary-500 hover:text-primary-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>

                <p class="text-sm text-gray-600">
                    This page shows all invoices that other businesses have issued to your business.
                    You can view the details of each invoice and dispute any invoice that you believe is incorrect.
                </p>

                @if(!$this->businessId)
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm text-blue-700">
                                    Tip: You can filter invoices by business by clicking on the "Business" filter above, or return to your <a href="{{ route('filament.client.pages.business-profile') }}#businesses-listed-you" class="font-medium underline">business profile</a> and select "View Invoices" for a specific business.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
