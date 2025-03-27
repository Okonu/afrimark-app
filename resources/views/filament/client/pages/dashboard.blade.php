{{-- resources/views/filament/client/pages/dashboard.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
{{--        <h1 class="text-2xl font-bold tracking-tight"></h1>--}}

        <!-- Onboarding Progress -->
        <div class="space-y-6 max-w-full">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold tracking-tight">Onboarding Progress</h2>

                @if(isset($nextStepUrl) && $nextStepUrl)
                    <x-filament::button
                        tag="a"
                        href="{{ $nextStepUrl }}"
                        color="primary"
                    >
                        Complete Next Step
                    </x-filament::button>
                @endif
            </div>

            @if(!$business)
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">
                        Please complete your business registration to track your onboarding progress.
                    </p>
                </div>
            @else
                <div class="space-y-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div
                            class="bg-primary-600 h-3 rounded-full transition-all duration-500 ease-in-out"
                            style="width: {{ $progress['percentage'] }}%"
                        ></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="p-4 rounded-lg border {{ $progress['steps']['business_info'] ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex items-start">
                                @if($progress['steps']['business_info'])
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                                @else
                                    <x-heroicon-s-x-circle class="w-5 h-5 text-gray-400 mr-2" />
                                @endif
                                <div>
                                    <h3 class="font-medium">Business Information</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $progress['steps']['business_info'] ? 'Completed' : 'Pending' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-lg border {{ $progress['steps']['email_verified'] ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex items-start">
                                @if($progress['steps']['email_verified'])
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                                @else
                                    <x-heroicon-s-x-circle class="w-5 h-5 text-gray-400 mr-2" />
                                @endif
                                <div>
                                    <h3 class="font-medium">Email Verification</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $progress['steps']['email_verified'] ? 'Verified' : 'Not Verified' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-lg border {{ $progress['steps']['documents_uploaded'] ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex items-start">
                                @if($progress['steps']['documents_uploaded'])
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                                @else
                                    <x-heroicon-s-x-circle class="w-5 h-5 text-gray-400 mr-2" />
                                @endif
                                <div>
                                    <h3 class="font-medium">Business Documents</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $progress['steps']['documents_uploaded'] ? 'Uploaded' : 'Not Uploaded' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-lg border {{ $progress['steps']['debtors_added'] ? 'border-green-500 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex items-start">
                                @if($progress['steps']['debtors_added'])
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-green-500 mr-2" />
                                @else
                                    <x-heroicon-s-x-circle class="w-5 h-5 text-gray-400 mr-2" />
                                @endif
                                <div>
                                    <h3 class="font-medium">Debtors Information</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $progress['steps']['debtors_added'] ? 'Added' : 'Not Added' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Debtor Analytics -->
        <div>
            <h2 class="text-xl font-bold tracking-tight mb-4">Your Debtor Analytics</h2>

            <!-- Top Row Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-1 mb-4">
                <!-- Total Debtors -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm font-medium text-gray-500">Total Debtors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $analytics['total_debtors'] ?? 0 }}</p>
                </div>

                <!-- Total Outstanding -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm font-medium text-gray-500">Total Outstanding Amount Owed</p>
                    <p class="text-2xl font-bold text-gray-900">KES {{ number_format($analytics['total_outstanding'] ?? 0, 2) }}</p>
                </div>

                <!-- Current -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm font-medium text-gray-500">Total Current Invoices Amount</p>
                    <p class="text-2xl font-bold text-green-600">KES {{ number_format($analytics['current_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $analytics['current_invoices'] ?? 0 }} invoices</p>
                </div>

                <!-- Overdue -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <p class="text-sm font-medium text-gray-500">Total Overdue Invoices Amount</p>
                    <p class="text-2xl font-bold text-red-600">KES {{ number_format($analytics['overdue_amount'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $analytics['overdue_invoices'] ?? 0 }} invoices</p>
                </div>
            </div>

            <!-- Analysis and Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Aging Analysis -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Aging Analysis</h3>

                    <div class="space-y-4">
                        @foreach(['current', '1-30', '31-60', '61-90', '90+'] as $range)
                            @php
                                $ageData = $analytics['by_age'][$range] ?? ['amount' => 0, 'count' => 0, 'percentage' => 0];
                                $label = $range === 'current' ? 'Current' : $range . ' days';

                                $barColor = match($range) {
                                    'current' => 'bg-green-500',
                                    '1-30' => 'bg-blue-500',
                                    '31-60' => 'bg-yellow-500',
                                    '61-90' => 'bg-orange-500',
                                    '90+' => 'bg-red-500',
                                    default => 'bg-gray-500',
                                };
                            @endphp

                            <div>
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium">{{ $label }}</span>
                                    <div class="flex items-center">
                                        <span class="font-medium">KES {{ number_format($ageData['amount'], 2) }}</span>
                                        <span class="text-xs text-gray-500 ml-1">({{ number_format($ageData['percentage'], 1) }}%)</span>
                                    </div>
                                </div>
                                <div class="mt-1 flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ min(100, $ageData['percentage']) }}%"></div>
                                    </div>
                                    <span class="ml-2 text-xs text-gray-500">{{ $ageData['count'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Status Breakdown -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                    <h3 class="text-base font-medium text-gray-900 mb-4">Status Breakdown</h3>

                    <div class="space-y-4">
                        @foreach(['active', 'disputed', 'pending', 'paid'] as $status)
                            @php
                                $statusData = $analytics['by_status'][$status] ?? ['count' => 0, 'amount' => 0];
                                $totalAmount = $analytics['total_outstanding'] ?? 0;
                                $percentage = $totalAmount > 0 ? ($statusData['amount'] / $totalAmount) * 100 : 0;

                                $iconColor = match($status) {
                                    'active' => 'text-green-600 bg-green-100',
                                    'disputed' => 'text-red-600 bg-red-100',
                                    'pending' => 'text-yellow-600 bg-yellow-100',
                                    'paid' => 'text-blue-600 bg-blue-100',
                                    default => 'text-gray-600 bg-gray-100',
                                };

                                $barColor = match($status) {
                                    'active' => 'bg-green-600',
                                    'disputed' => 'bg-red-600',
                                    'pending' => 'bg-yellow-600',
                                    'paid' => 'bg-blue-600',
                                    default => 'bg-gray-600',
                                };
                            @endphp

                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center p-2 rounded-md {{ $iconColor }}">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            @if($status === 'active')
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            @elseif($status === 'disputed')
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            @elseif($status === 'pending')
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            @elseif($status === 'paid')
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            @endif
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-3 flex-1">
                                    <div class="flex justify-between">
                                        <span class="text-sm font-medium capitalize">{{ $status }}</span>
                                        <span class="text-sm font-medium">{{ $statusData['count'] }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ min(100, $percentage) }}%"></div>
                                        </div>
                                        <span class="ml-2 text-xs text-gray-500">KES {{ number_format($statusData['amount'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
