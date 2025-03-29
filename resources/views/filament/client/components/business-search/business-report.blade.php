<!-- components/business-search/business-report.blade.php -->
<x-filament::section>
    <!-- Header with navigation -->
    @include('filament.client.components.business-search.report-header')

    <!-- Credit Score Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Credit Score Card -->
{{--        @include('filament.client.components.business-search.report-score-card')--}}

        <!-- Financial Summary Card -->
        @include('filament.client.components.business-search.report-financial-card')

        <!-- Listings Summary Card -->
        @include('filament.client.components.business-search.report-listings-card')
    </div>

    <!-- Credit Score Components Section -->
    @if(isset($businessReport['has_api_score']) && $businessReport['has_api_score'] && isset($businessReport['api_score_details']) && is_array($businessReport['api_score_details']))
        @include('filament.client.components.business-search.report-score-breakdown')
    @endif

    <!-- Report Explanation Section -->
    @include('filament.client.components.business-search.report-explanation')
</x-filament::section>
