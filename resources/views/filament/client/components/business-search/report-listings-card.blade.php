<!-- components/business-search/report-listings-card.blade.php -->
<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-700">Listings Summary</h3>
    </div>
    <div class="p-4">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500">Active Listings</span>
                    <p class="text-lg font-semibold {{ isset($businessReport['active_listings']) && $businessReport['active_listings'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $businessReport['active_listings'] ?? 0 }}
                    </p>
                </div>
                <div class="h-10 w-10 flex items-center justify-center rounded-full {{ isset($businessReport['active_listings']) && $businessReport['active_listings'] > 0 ? 'bg-red-100' : 'bg-gray-100' }}">
                    <x-heroicon-s-exclamation-circle class="{{ isset($businessReport['active_listings']) && $businessReport['active_listings'] > 0 ? 'text-red-500' : 'text-gray-400' }} h-6 w-6" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500">Resolved Listings</span>
                    <p class="text-lg font-semibold {{ isset($businessReport['resolved_listings']) && $businessReport['resolved_listings'] > 0 ? 'text-green-600' : 'text-gray-900' }}">
                        {{ $businessReport['resolved_listings'] ?? 0 }}
                    </p>
                </div>
                <div class="h-10 w-10 flex items-center justify-center rounded-full {{ isset($businessReport['resolved_listings']) && $businessReport['resolved_listings'] > 0 ? 'bg-green-100' : 'bg-gray-100' }}">
                    <x-heroicon-s-check-circle class="{{ isset($businessReport['resolved_listings']) && $businessReport['resolved_listings'] > 0 ? 'text-green-500' : 'text-gray-400' }} h-6 w-6" />
                </div>
            </div>

            <div>
                <span class="text-xs font-medium text-gray-500">Resolution Rate</span>
                <p class="text-lg font-semibold text-gray-900">
                    @php
                        $activeListings = $businessReport['active_listings'] ?? 0;
                        $resolvedListings = $businessReport['resolved_listings'] ?? 0;
                        $totalListings = $activeListings + $resolvedListings;
                        $resolutionRate = $totalListings > 0 ? ($resolvedListings / $totalListings) * 100 : 0;
                    @endphp
                    {{ number_format($resolutionRate, 1) }}%
                </p>
            </div>
        </div>
    </div>
</div>
