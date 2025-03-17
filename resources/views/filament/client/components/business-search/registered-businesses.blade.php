<!-- components/business-search/registered-businesses.blade.php -->
<div class="mb-8">
    <div class="flex items-center space-x-2 mb-4">
        <div class="bg-primary-50 text-primary-700 p-1.5 rounded-lg">
            <x-heroicon-s-building-office class="h-5 w-5" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Registered Businesses</h3>
        <x-filament::badge color="primary">{{ count($searchResults['registered']) }}</x-filament::badge>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($searchResults['registered'] as $business)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-200">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-lg text-gray-900 line-clamp-1">{{ $business['name'] }}</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="inline-flex items-center">
                                    <x-heroicon-s-identification class="h-4 w-4 mr-1" />
                                    {{ $business['registration_number'] }}
                                </span>
                            </p>
                        </div>
                        <x-filament::badge color="success">Registered</x-filament::badge>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-filament::button
                            color="primary"
                            size="sm"
                            icon="heroicon-m-document-chart-bar"
                            wire:click="viewBusinessReport('registered', {{ $business['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="viewBusinessReport('registered', {{ $business['id'] }})"
                        >
                            <span wire:loading.remove wire:target="viewBusinessReport('registered', {{ $business['id'] }})">
                                View Credit Report
                            </span>
                            <span wire:loading wire:target="viewBusinessReport('registered', {{ $business['id'] }})">
                                Loading...
                            </span>
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
