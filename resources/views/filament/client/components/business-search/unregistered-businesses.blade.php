<!-- components/business-search/unregistered-businesses.blade.php -->
<div class="mb-6">
    <div class="flex items-center space-x-2 mb-4">
        <div class="bg-amber-50 text-amber-700 p-1.5 rounded-lg">
            <x-heroicon-s-building-storefront class="h-5 w-5" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900">Unregistered Listed Businesses</h3>
        <x-filament::badge color="warning">{{ count($searchResults['unregistered_listed']) }}</x-filament::badge>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($searchResults['unregistered_listed'] as $business)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-200">
                <div class="p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-medium text-lg text-gray-900 line-clamp-1">{{ $business['name'] }}</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="inline-flex items-center">
                                    <x-heroicon-s-identification class="h-4 w-4 mr-1" />
                                    {{ $business['kra_pin'] ?? 'KRA PIN not available' }}
                                </span>
                            </p>
                        </div>
                        <x-filament::badge color="warning">Unregistered</x-filament::badge>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button
                            class="inline-flex items-center justify-center rounded-lg border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-70"
                            wire:click="viewBusinessReport('unregistered', {'name': '{{ $business['name'] }}', 'kra_pin': '{{ $business['kra_pin'] ?? '' }}'})"
                        >
                            <x-heroicon-o-document-chart-bar class="mr-1 h-4 w-4" />
                            View Credit Report
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
