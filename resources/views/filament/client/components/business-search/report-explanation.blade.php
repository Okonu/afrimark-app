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
                    based on payment history, financial behavior, and credit factors. The credit score ranges from 0 to 100,
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
                        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <x-heroicon-s-identification class="h-5 w-5" />
                        </div>
                        <h4 class="font-medium text-gray-900">Business Listings</h4>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">Network records:</p>
                        <span class="text-xl font-bold text-blue-600">
                            {{ $businessReport['total_listings'] ?? 0 }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        The number of businesses in our network that have registered transactions with this entity.
                        Listings are neutral records and do not inherently indicate positive or negative status.
                    </p>

                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center">
                                <div class="h-2 w-2 rounded-full bg-green-500 mr-1"></div>
                                <span>Positive:</span>
                            </div>
                            <span class="font-medium">{{ $businessReport['positive_listings'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs mt-1">
                            <div class="flex items-center">
                                <div class="h-2 w-2 rounded-full bg-red-500 mr-1"></div>
                                <span>Negative:</span>
                            </div>
                            <span class="font-medium">{{ $businessReport['negative_listings'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-{{ isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                            1, 2 => 'green-100 text-green-600',
                            3 => 'yellow-100 text-yellow-600',
                            4, 5 => 'red-100 text-red-600',
                            default => 'gray-100 text-gray-600',
                        } : 'gray-100 text-gray-600' }} rounded-lg">
                            <x-heroicon-s-shield-check class="h-5 w-5" />
                        </div>
                        <h4 class="font-medium text-gray-900">Risk Level</h4>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-gray-600">Credit category:</p>
                        <span class="text-sm font-bold {{ isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                            1, 2 => 'text-green-600',
                            3 => 'text-yellow-600',
                            4, 5 => 'text-red-600',
                            default => 'text-gray-600',
                        } : 'text-gray-600' }}">
                            {{ $businessReport['risk_description'] ?? 'Unknown' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        The overall risk assessment based on the credit score and financial behavior analysis.
                    </p>
                </div>
            </div>

            @php
                $activeListings = $businessReport['active_listings'] ?? 0;
                $resolvedListings = $businessReport['resolved_listings'] ?? 0;
                $totalListings = $businessReport['total_listings'] ?? 0;
                $disputedListings = $businessReport['disputed_listings'] ?? 0;
            @endphp

            <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                <h4 class="font-medium text-gray-800 mb-3">Credit Risk Factors</h4>

                <div class="text-xs text-gray-600">
                    @if(isset($businessReport['api_score_details']['Reasons for score']))
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="font-medium text-gray-700 mb-1">Risk Analysis</p>
                            <p>{{ $businessReport['api_score_details']['Reasons for score'] }}</p>
                        </div>
                    @endif

                    @if(isset($businessReport['api_score_details']['Normalized PD']))
                        <div class="mb-4">
                            <p class="font-medium text-gray-700 mb-1">Probability of Default</p>
                            <div class="flex items-center">
                                <div class="w-full">
                                    <div class="flex justify-between items-center mb-1">
                                        <span>Default Probability</span>
                                        <span class="font-medium {{ (float)$businessReport['api_score_details']['Normalized PD'] > 0.5 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ number_format($businessReport['api_score_details']['Normalized PD'] * 100, 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="rounded-full h-2 {{ (float)$businessReport['api_score_details']['Normalized PD'] > 0.5 ? 'bg-red-500' : 'bg-green-500' }}"
                                             style="width: {{ min(100, max(0, (float)$businessReport['api_score_details']['Normalized PD'] * 100)) }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <p class="font-medium text-gray-700 mb-1">Listing Breakdown</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span>Positive Listings</span>
                                    <span class="font-medium text-green-600">
                                        {{ $businessReport['positive_listings'] ?? 0 }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-xs">
                                    Businesses that have listed this entity with no overdue invoices
                                </p>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span>Negative Listings</span>
                                    <span class="font-medium text-red-600">
                                        {{ $businessReport['negative_listings'] ?? 0 }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-xs">
                                    Businesses that have listed this entity with at least one overdue invoice
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($disputedListings > 0)
                        <p class="mt-2 text-amber-600">
                            <span class="font-medium">Note:</span> There {{ $disputedListings == 1 ? 'is' : 'are' }} currently {{ $disputedListings }}
                            record{{ $disputedListings == 1 ? '' : 's' }} under dispute. Disputed records are being reviewed and aren't factored into the credit score.
                        </p>
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
                                risk classification, and your own risk tolerance.
                            </p>
                            <p>
                                <span class="font-medium">Recommendation Based on Credit Score:</span>
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
