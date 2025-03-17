<!-- components/business-search/report-explanation.blade.php -->
<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center space-x-2">
            <x-heroicon-s-academic-cap class="h-5 w-5 text-primary-500" />
            <h3 class="font-medium text-gray-900">Understanding this Credit Report</h3>
        </div>
    </div>

    <div class="p-6">
        <div class="text-sm text-gray-600 space-y-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="mb-3">
                    This credit report provides an assessment of <span class="font-medium text-gray-800">{{ $businessReport['name'] ?? 'this business' }}</span>'s creditworthiness
                    based on payment history, financial behavior, and debt obligations. The credit score ranges from 0 to 100,
                    with higher scores indicating better creditworthiness.
                </p>

                @if(isset($businessReport['credit_score']) && $businessReport['credit_score'] !== null)
                    <div class="flex items-center bg-gray-50 p-3 rounded-lg border border-gray-200 mb-3">
                        <div class="h-10 w-10 rounded-full
                            {{ match(true) {
                                isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 80 => 'bg-green-100',
                                isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 60 => 'bg-yellow-100',
                                default => 'bg-red-100',
                            } }}
                            flex items-center justify-center mr-3">
                            <span class="text-lg font-bold
                                {{ match(true) {
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 80 => 'text-green-600',
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 60 => 'text-yellow-600',
                                    default => 'text-red-600',
                                } }}">
                                {{ number_format($businessReport['credit_score'], 0) }}
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Current Credit Score</p>
                            <p class="text-xs text-gray-500">
                                A score of {{ number_format($businessReport['credit_score'], 0) }} is considered
                                {{ match(true) {
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 80 => 'excellent',
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 70 => 'good',
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 60 => 'fair',
                                    isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 40 => 'poor',
                                    default => 'very poor',
                                } }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="p-4 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                            <x-heroicon-s-exclamation-circle class="h-5 w-5" />
                        </div>
                        <h4 class="font-medium text-gray-900">Active Listings</h4>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">Unpaid debts or invoices:</p>
                        <span class="text-xl font-bold {{ isset($businessReport['active_listings']) && $businessReport['active_listings'] > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            {{ $businessReport['active_listings'] ?? 0 }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Each active listing negatively impacts the overall credit score.</p>
                </div>

                <div class="p-4 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                            <x-heroicon-s-check-circle class="h-5 w-5" />
                        </div>
                        <h4 class="font-medium text-gray-900">Resolved Listings</h4>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">Paid or settled debts:</p>
                        <span class="text-xl font-bold {{ isset($businessReport['resolved_listings']) && $businessReport['resolved_listings'] > 0 ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $businessReport['resolved_listings'] ?? 0 }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Demonstrates willingness to pay debts, positively affecting credit score.</p>
                </div>

                <div class="p-4 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <x-heroicon-s-banknotes class="h-5 w-5" />
                        </div>
                        <h4 class="font-medium text-gray-900">Total Amount Owed</h4>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">Current debt:</p>
                        <span class="text-xl font-bold {{ isset($businessReport['total_owed']) && $businessReport['total_owed'] > 0 ? 'text-red-600' : 'text-gray-500' }}">
                            KES {{ number_format($businessReport['total_owed'] ?? 0, 0) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Higher debt amounts can indicate greater financial risk.</p>
                </div>
            </div>

            @php
                $activeListings = $businessReport['active_listings'] ?? 0;
                $resolvedListings = $businessReport['resolved_listings'] ?? 0;
                $totalListings = $activeListings + $resolvedListings;
                $resolutionRate = $totalListings > 0 ? ($resolvedListings / $totalListings) * 100 : 0;
            @endphp

            <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                <h4 class="font-medium text-gray-800 mb-3">Payment Resolution Analysis</h4>

                <div class="flex items-center mb-3">
                    <div class="w-full">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-medium text-gray-500">Resolution Rate</span>
                            <span class="text-xs font-medium text-gray-900">{{ number_format($resolutionRate, 1) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="rounded-full h-2
                                {{ match(true) {
                                    $resolutionRate >= 70 => 'bg-green-500',
                                    $resolutionRate >= 40 => 'bg-yellow-500',
                                    default => 'bg-red-500',
                                } }}"
                                 style="width: {{ $resolutionRate }}%">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-gray-600">
                    <p>This business has resolved {{ $resolvedListings }} out of {{ $totalListings }} total listings,
                        resulting in a {{ number_format($resolutionRate, 1) }}% resolution rate.</p>

                    @if($resolutionRate >= 70)
                        <p class="mt-2 text-green-600">✓ <span class="font-medium">Excellent resolution rate</span>: This business has demonstrated strong commitment to settling their obligations.</p>
                    @elseif($resolutionRate >= 40)
                        <p class="mt-2 text-yellow-600">⚠ <span class="font-medium">Average resolution rate</span>: This business has settled some past obligations but may need monitoring.</p>
                    @else
                        <p class="mt-2 text-red-600">⚠ <span class="font-medium">Poor resolution rate</span>: This business has a history of leaving obligations unsettled.</p>
                    @endif
                </div>
            </div>

            <div class="mt-2 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <x-heroicon-s-light-bulb class="h-6 w-6 text-yellow-500" />
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">Making Business Decisions</h4>
                        <div class="mt-2 text-xs text-yellow-700 space-y-2">
                            <p>
                                When deciding whether to do business with this entity, consider all factors including credit score,
                                payment history, total debt, and your own risk tolerance.
                            </p>
                            <p>
                                <span class="font-medium">Business Recommendation:</span>
                                @if(isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 80)
                                    This business presents minimal credit risk. Standard business terms are appropriate.
                                @elseif(isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 60)
                                    This business presents moderate credit risk. Consider standard terms with regular monitoring.
                                @elseif(isset($businessReport['credit_score']) && $businessReport['credit_score'] >= 40)
                                    This business presents elevated credit risk. Consider shortened payment terms or partial advance payment.
                                @else
                                    This business presents significant credit risk. Consider upfront payment or secured credit arrangements.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
