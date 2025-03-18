<!-- components/business-search/report-score-card.blade.php -->
<div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <h3 class="text-sm font-medium text-gray-700">Credit Score</h3>
    </div>
    <div class="p-4">
        @if(isset($businessReport['credit_score']) && $businessReport['credit_score'] !== null)
            <div class="flex flex-col items-center text-center">
                <div class="relative mb-3">
                    <svg class="w-24 h-24" viewBox="0 0 36 36">
                        <path
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none"
                            stroke="#e5e7eb"
                            stroke-width="3"
                            stroke-dasharray="100, 100"
                        />
                        <path
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none"
                            stroke="{{ match(isset($businessReport['risk_class']) ? (int)$businessReport['risk_class'] : 0) {
                                1 => '#10b981',
                                2 => '#22c55e',
                                3 => '#eab308',
                                4 => '#f59e0b',
                                5 => '#ef4444',
                                default => '#6b7280',
                            } }}"
                            stroke-width="3"
                            stroke-dasharray="{{ min(100, max(0, $businessReport['credit_score'])) }}, 100"
                            stroke-linecap="round"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold {{ match(isset($businessReport['risk_class']) ? (int)$businessReport['risk_class'] : 0) {
                            1, 2 => 'text-green-600',
                            3 => 'text-yellow-600',
                            4, 5 => 'text-red-600',
                            default => 'text-gray-700',
                        } }}">{{ number_format($businessReport['credit_score'], 0) }}</span>
                    </div>
                </div>

                <div class="mt-1 mb-2">
                    <x-filament::badge color="{{ match(isset($businessReport['risk_class']) ? (int)$businessReport['risk_class'] : 0) {
                        1, 2 => 'success',
                        3 => 'warning',
                        4, 5 => 'danger',
                        default => 'gray',
                    } }}">
                        {{ $businessReport['risk_description'] ?? $this->getCreditScoreDescription($businessReport) }}
                    </x-filament::badge>
                </div>

                @if(isset($businessReport['has_api_score']) && $businessReport['has_api_score'])
                    <p class="text-xs text-gray-500 italic">Risk Level Score</p>
                @endif
            </div>
        @else
            <div class="flex justify-center items-center h-24">
                <span class="text-gray-500">No credit score available</span>
            </div>
        @endif
    </div>
</div>
