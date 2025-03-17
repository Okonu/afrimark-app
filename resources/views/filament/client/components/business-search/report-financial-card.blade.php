<!-- components/business-search/report-financial-card.blade.php -->
<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-700">Financial Summary</h3>
    </div>
    <div class="p-4">
        <div class="space-y-4">
            <div>
                <span class="text-xs font-medium text-gray-500">Total Amount Owed</span>
                <p class="text-lg font-semibold {{ isset($businessReport['total_owed']) && $businessReport['total_owed'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                    KES {{ number_format($businessReport['total_owed'] ?? 0, 2) }}
                </p>
            </div>

            @if(isset($businessReport['api_score_details']['Normalized PD']))
                <div>
                    <span class="text-xs font-medium text-gray-500">Default Probability</span>
                    <p class="text-lg font-semibold {{ $businessReport['api_score_details']['Normalized PD'] > 0.2 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($businessReport['api_score_details']['Normalized PD'] * 100, 1) }}%
                    </p>
                </div>
            @endif

            @if(isset($businessReport['api_score_details']['Reasons for score']))
                <div>
                    <span class="text-xs font-medium text-gray-500">Risk Factors</span>
                    <p class="text-sm font-medium text-gray-900">
                        {{ $businessReport['api_score_details']['Reasons for score'] }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
