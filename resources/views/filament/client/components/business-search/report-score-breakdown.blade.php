<!-- components/business-search/report-score-breakdown.blade.php -->
<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <x-heroicon-s-chart-bar class="h-5 w-5 text-primary-500" />
            <h3 class="font-medium text-gray-900">Credit Risk Assessment</h3>
        </div>
        @if(isset($businessReport['api_score_details']['Timestamp']))
            <span class="text-xs text-gray-500 flex items-center">
                <x-heroicon-s-clock class="h-4 w-4 mr-1" />
                Last updated: {{ date('M d, Y H:i', strtotime($businessReport['api_score_details']['Timestamp'])) }}
            </span>
        @endif
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Score -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500 mb-1">Composite Score</p>
                <div class="flex items-end">
                    <span class="text-4xl font-bold text-{{ isset($businessReport['risk_color']) ? $businessReport['risk_color'] : (isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                        1 => 'success',
                        2 => 'success',
                        3 => 'warning',
                        4 => 'danger',
                        5 => 'danger',
                        default => 'gray',
                    } : 'gray') }}-600">
                        {{ isset($businessReport['credit_score']) ? number_format((float)$businessReport['credit_score'], 1) : 'N/A' }}
                    </span>
{{--                    <span class="text-sm text-gray-500 ml-2 mb-1">/100</span>--}}
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-{{ isset($businessReport['risk_color']) ? $businessReport['risk_color'] : (isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                            1 => 'success',
                            2 => 'success',
                            3 => 'warning',
                            4 => 'danger',
                            5 => 'danger',
                            default => 'gray',
                        } : 'gray') }}-600 h-2.5 rounded-full"
                             style="width: {{ isset($businessReport['credit_score']) ? min(100, max(0, (float)$businessReport['credit_score'])) : 0 }}%"></div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ isset($businessReport['risk_color']) ? $businessReport['risk_color'] : (isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                        1 => 'success',
                        2 => 'success',
                        3 => 'warning',
                        4 => 'danger',
                        5 => 'danger',
                        default => 'gray',
                    } : 'gray') }}-100 text-{{ isset($businessReport['risk_color']) ? $businessReport['risk_color'] : (isset($businessReport['risk_class']) ? match((int)$businessReport['risk_class']) {
                        1 => 'success',
                        2 => 'success',
                        3 => 'warning',
                        4 => 'danger',
                        5 => 'danger',
                        default => 'gray',
                    } : 'gray') }}-800">
                        {{ $businessReport['risk_description'] ?? 'Unknown' }} Risk (Class {{ $businessReport['risk_class'] ?? '?' }})
                    </span>
                </div>
            </div>

            <!-- Component Scores -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500 mb-2">Component Scores</p>

                <div class="space-y-3">
                    @if(isset($businessReport['api_score_details']['PPS']))
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium text-gray-700">Payment Probability (PPS)</span>
                                <span class="text-xs font-medium">{{ number_format($businessReport['api_score_details']['PPS'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-blue-600 h-1.5 rounded-full"
                                     style="width: {{ min(100, max(0, $businessReport['api_score_details']['PPS'])) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">60% of composite score</p>
                        </div>
                    @endif

                    @if(isset($businessReport['api_score_details']['Exposure Score']))
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium text-gray-700">Exposure Score</span>
                                <span class="text-xs font-medium">{{ number_format($businessReport['api_score_details']['Exposure Score'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-green-600 h-1.5 rounded-full"
                                     style="width: {{ min(100, max(0, $businessReport['api_score_details']['Exposure Score'])) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">20% of composite score</p>
                        </div>
                    @endif

                    @if(isset($businessReport['api_score_details']['V']))
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs font-medium text-gray-700">Business Verification(V)</span>
                                <span class="text-xs font-medium">{{ number_format($businessReport['api_score_details']['V'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="bg-purple-600 h-1.5 rounded-full"
                                     style="width: {{ min(100, max(0, $businessReport['api_score_details']['V'])) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">20% of composite score</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Risk Assessment Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500 mb-2">Risk Assessment Details</p>

                <div class="space-y-4">
                    @if(isset($businessReport['api_score_details']['Reasons for score']))
                        <div>
{{--                            <p class="text-xs text-gray-500">Risk Reasons</p>--}}
                            <p class="font-medium text-sm">{{ $businessReport['api_score_details']['Reasons for score'] }}</p>
                        </div>
                    @endif

                    @if(isset($businessReport['api_score_details']['Normalized PD']))
                        <div>
                            <p class="text-xs text-gray-500">Probability of Default</p>
                            <p class="font-medium text-sm {{ (float)$businessReport['api_score_details']['Normalized PD'] > 0.5 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($businessReport['api_score_details']['Normalized PD'] * 100, 1) }}%
                            </p>
                        </div>
                    @endif

                    @if(isset($businessReport['api_score_details']['Total Amount Owed']))
                        <div>
                            <p class="text-xs text-gray-500">Total Amount Owed</p>
                            <p class="font-medium text-sm">
                                KES {{ number_format($businessReport['api_score_details']['Total Amount Owed'], 2) }}
                            </p>
                        </div>
                    @else
                        <div>
                            <p class="text-xs text-gray-500">Total Amount Owed</p>
                            <p class="font-medium text-sm">
                                KES {{ number_format($businessReport['total_owed'] ?? 0, 2) }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Risk Class Explanation -->
        <div class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-800 mb-2">Risk Classification</h4>

            <div class="text-sm text-gray-600">
                @if(isset($businessReport['risk_class']))
                    @if((int)$businessReport['risk_class'] == 1)
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <p><span class="font-medium text-green-700">Very Low Risk (Class 1):</span> Excellent payment history and strong financial stability.</p>
                        </div>
                    @elseif((int)$businessReport['risk_class'] == 2)
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <p><span class="font-medium text-green-700">Low to Medium Risk (Class 2):</span> Good payment history with minor concerns.</p>
                        </div>
                    @elseif((int)$businessReport['risk_class'] == 3)
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <p><span class="font-medium text-yellow-700">Medium Risk (Class 3):</span> Average payment history with some financial concerns.</p>
                        </div>
                    @elseif((int)$businessReport['risk_class'] == 4)
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                            <p><span class="font-medium text-orange-700">Medium to High Risk (Class 4):</span> Below average payment history with notable financial concerns.</p>
                        </div>
                    @elseif((int)$businessReport['risk_class'] == 5)
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <p><span class="font-medium text-red-700">High Risk (Class 5):</span> Poor payment history with significant financial concerns.</p>
                        </div>
                    @endif
                @else
                    <p>Risk classification information is not available for this business.</p>
                @endif
            </div>
        </div>

        <div class="mt-4 text-sm text-gray-500">
            <p>This credit score is based on the business's payment history across our network.</p>
        </div>
    </div>
</div>
