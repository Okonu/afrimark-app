<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header with search form -->
        @include('filament.client.components.business-search.header')

        <!-- Search form -->
        @include('filament.client.components.business-search.search-form')

        <!-- Search Results Section -->
        @if($searchResults)
            @include('filament.client.components.business-search.search-results')
        @elseif($searchPerformed)
            @include('filament.client.components.business-search.no-results')
        @endif

        <!-- Business Report Section -->
        @if($businessReport)
            @include('filament.client.components.business-search.business-report')
        @endif
    </div>
</x-filament-panels::page>
