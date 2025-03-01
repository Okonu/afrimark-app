<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit="search">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Search</span>
                    <span wire:loading>Searching...</span>
                </x-filament::button>
            </div>
        </form>

        @if($searchResults)
            <div class="mt-6">
                <x-filament::section>
                    <h2 class="text-xl font-bold tracking-tight mb-4">Search Results</h2>

                    @if(count($searchResults['registered']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-2">Registered Businesses</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($searchResults['registered'] as $business)
                                    <div class="border rounded-md p-4 bg-white">
                                        <h4 class="font-medium">{{ $business['name'] }}</h4>
                                        <p class="text-sm text-gray-600">KRA PIN: {{ $business['registration_number'] }}</p>
                                        <div class="mt-3">
                                            <x-filament::button
                                                size="sm"
                                                wire:click="viewBusinessReport('registered', {{ $business['id'] }})"
                                            >
                                                View Report
                                            </x-filament::button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($searchResults['unregistered_listed']) > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-2">Unregistered Listed Businesses</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($searchResults['unregistered_listed'] as $business)
                                    <div class="border rounded-md p-4 bg-white">
                                        <h4 class="font-medium">{{ $business['name'] }}</h4>
                                        <p class="text-sm text-gray-600">KRA PIN: {{ $business['kra_pin'] }}</p>
                                        <div class="mt-3">
                                            <x-filament::button
                                                size="sm"
                                                wire:click="viewBusinessReport('unregistered', {{ json_encode($business) }})"
                                            >
                                                View Report
                                            </x-filament::button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(count($searchResults['no_report']) > 0)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-gray-600">
                                No information found for "{{ $searchResults['no_report'][0]['search_term'] }}".
                            </p>
                        </div>
                    @endif
                </x-filament::section>
            </div>
        @endif

        @if($businessReport)
            <div class="mt-6">
                <x-filament::section>
                    <h2 class="text-xl font-bold tracking-tight mb-4">
                        Business Report: {{ $selectedBusiness['name'] }}
                    </h2>

                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Business Information</h3>
                                    <div class="mt-2 border-t border-gray-200 pt-2">
                                        <div class="grid grid-cols-2 gap-y-2">
                                            <div class="text-sm font-medium text-gray-500">Name</div>
                                            <div class="text-sm text-gray-900">{{ $businessReport['name'] }}</div>

                                            <div class="text-sm font-medium text-gray-500">
                                                {{ $businessReport['is_registered'] ? 'Registration Number' : 'KRA PIN' }}
                                            </div>
                                            <div class="text-sm text-gray-900">
                                                {{ $businessReport['is_registered'] ? $businessReport['registration_number'] : $businessReport['kra_pin'] }}
                                            </div>

                                            <div class="text-sm font-medium text-gray-500">Registration Status</div>
                                            <div class="text-sm text-gray-900">
                                                @if($businessReport['is_registered'])
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Registered
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Unregistered
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-base font-medium text-gray-900">Credit Information</h3>
                                    <div class="mt-2 border-t border-gray-200 pt-2">
                                        <div class="grid grid-cols-2 gap-y-2">
                                            <div class="text-sm font-medium text-gray-500">Credit Score</div>
                                            <div class="text-sm text-gray-900">
                                                <div class="relative w-full h-4 bg-gray-200 rounded-full">
                                                    @php
                                                        $scorePercent = min(100, max(0, $businessReport['credit_score'] / 850 * 100));
                                                        $scoreColor = 'bg-red-500';

                                                        if ($scorePercent >= 70) {
                                                            $scoreColor = 'bg-green-500';
                                                        } elseif ($scorePercent >= 40) {
                                                            $scoreColor = 'bg-yellow-500';
                                                        }
                                                    @endphp
                                                    <div class="{{ $scoreColor }} h-4 rounded-full" style="width: {{ $scorePercent }}%"></div>
                                                </div>
                                                <div class="mt-1 text-sm">{{ $businessReport['credit_score'] }} / 850</div>
                                            </div>

                                            <div class="text-sm font-medium text-gray-500">Total Amount Owed</div>
                                            <div class="text-sm text-gray-900">{{ number_format($businessReport['total_owed'], 2) }} KES</div>

                                            <div class="text-sm font-medium text-gray-500">Active Listings</div>
                                            <div class="text-sm text-gray-900">{{ $businessReport['active_listings'] }}</div>

                                            <div class="text-sm font-medium text-gray-500">Resolved Listings</div>
                                            <div class="text-sm text-gray-900">{{ $businessReport['resolved_listings'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
