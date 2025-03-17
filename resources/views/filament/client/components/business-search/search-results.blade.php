<!-- components/business-search/search-results.blade.php -->
<x-filament::section>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold tracking-tight text-gray-900">Search Results</h2>
        <span class="text-sm text-gray-500">
            Searched for: <span class="font-medium">{{ $data['search_term'] ?? '' }}</span>
        </span>
    </div>

    @if(count($searchResults['registered']) > 0 || count($searchResults['unregistered_listed']) > 0)
        <!-- Registered Businesses Section -->
        @if(count($searchResults['registered']) > 0)
            @include('filament.client.components.business-search.registered-businesses')
        @endif

        <!-- Unregistered Businesses Section -->
        @if(count($searchResults['unregistered_listed']) > 0)
            @include('filament.client.components.business-search.unregistered-businesses')
        @endif
    @else
        <!-- No Results Found -->
        <div class="flex flex-col items-center justify-center text-center p-8 bg-gray-50 rounded-lg border border-gray-200">
            <div class="bg-gray-100 p-3 rounded-full mb-4">
                <x-heroicon-o-magnifying-glass class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No results found</h3>
            <p class="text-gray-500 max-w-md">
                No information found for "<span class="font-medium">{{ $searchResults['no_report'][0]['search_term'] ?? $data['search_term'] }}</span>". Try using a different search term or check your spelling.
            </p>
            <x-filament::button color="gray" class="mt-4" icon="heroicon-m-arrow-path" wire:click="$set('data.search_term', '')">
                Try another search
            </x-filament::button>
        </div>
    @endif
</x-filament::section>
