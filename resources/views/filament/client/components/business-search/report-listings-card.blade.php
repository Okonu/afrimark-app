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
                        {{ $businessReport['total_listings'] ?? 0 }}
                    </p>
                </div>
                <div class="h-10 w-10 flex items-center justify-center rounded-full bg-blue-100">
                    <x-heroicon-s-clipboard-document-list class="text-blue-500 h-6 w-6" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-green-50 p-3 rounded-lg">
                    <span class="text-xs font-medium text-gray-500">Positive Listings</span>
                    <p class="text-lg font-semibold text-green-600">
                        {{ $businessReport['positive_listings'] ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Businesses with no overdue invoices
                    </p>
                </div>

                <div class="bg-red-50 p-3 rounded-lg">
                    <span class="text-xs font-medium text-gray-500">Negative Listings</span>
                    <p class="text-lg font-semibold text-red-600">
                        {{ $businessReport['negative_listings'] ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Businesses with overdue invoices
                    </p>
                </div>
            </div>

            <div>
                <span class="text-xs font-medium text-gray-500">Listed By</span>
                <p class="text-sm font-medium text-gray-900 mt-1">
                    This business has been listed by {{ $businessReport['total_listings'] ?? 0 }} {{ ($businessReport['total_listings'] ?? 0) == 1 ? 'company' : 'companies' }} in our network.
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Listings are records of business relationships in our system. A negative listing indicates overdue invoices, while a positive listing indicates no overdue invoices.
                </p>
            </div>

            <div class="border-t border-gray-200 pt-4 mt-4">
                <span class="text-xs font-medium text-gray-500">Listing Details</span>
                <p class="text-sm mt-2 text-gray-700">
                    Of the {{ $businessReport['total_listings'] ?? 0 }} {{ ($businessReport['total_listings'] ?? 0) == 1 ? 'business' : 'businesses' }} that have listed this company:
                </p>
                <div class="flex mt-2">
                    <div class="flex-1 flex items-center">
                        <div class="h-3 w-3 rounded-full bg-green-500 mr-2"></div>
                        <span class="text-sm text-gray-600">{{ $businessReport['positive_listings'] ?? 0 }} with no overdue invoices</span>
                    </div>
                    <div class="flex-1 flex items-center">
                        <div class="h-3 w-3 rounded-full bg-red-500 mr-2"></div>
                        <span class="text-sm text-gray-600">{{ $businessReport['negative_listings'] ?? 0 }} with overdue invoices</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
