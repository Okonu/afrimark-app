<!-- components/business-search/report-listings-card.blade.php -->
<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-700">Listings Summary</h3>
    </div>
    <div class="p-4">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500">Total Listings</span>
                    <p class="text-lg font-semibold text-blue-600">
                        {{ ($businessReport['active_listings'] ?? 0) + ($businessReport['resolved_listings'] ?? 0) }}
                    </p>
                </div>
                <div class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-100">
                    <x-heroicon-s-clipboard-document-list class="text-blue-500 h-6 w-6" />
                </div>
            </div>

            <div>
                <span class="text-xs font-medium text-gray-500">Listed By</span>
                <p class="text-sm font-medium text-gray-900 mt-1">
                    @php
                        $activeListings = $businessReport['active_listings'] ?? 0;
                        $totalListings = $activeListings + ($businessReport['resolved_listings'] ?? 0);
                    @endphp
                    This business has been listed by {{ $totalListings }} {{ $totalListings == 1 ? 'company' : 'companies' }} in our network.
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Listings are records of business relationships in our system and simply indicate that this entity has been registered as a transaction partner.
                </p>
            </div>

{{--            <div>--}}
{{--                <span class="text-xs font-medium text-gray-500">Total Amount</span>--}}
{{--                <p class="text-lg font-semibold text-blue-600">--}}
{{--                    KES {{ number_format($businessReport['total_owed'] ?? 0, 0) }}--}}
{{--                </p>--}}
{{--                <p class="text-xs text-gray-500 mt-1">--}}
{{--                    Combined value of all transactions recorded in our system.--}}
{{--                </p>--}}
{{--            </div>--}}
        </div>
    </div>
</div>
