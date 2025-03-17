@php use Illuminate\Support\Facades\DB; @endphp
<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->business)
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Business Information Card -->
                <div class="md:w-1/3 space-y-4">
                    <x-filament::section>
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold">{{ $this->business->name }}</h2>
                            @php
                                $allDocumentsVerified = $this->business->documents()->count() > 0 &&
                                    $this->business->documents()->whereNull('verified_at')->count() === 0;
                            @endphp

                            @if($allDocumentsVerified)
                                <div class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                    <span class="inline-flex items-center">
                                        <svg class="mr-1 h-3 w-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Verified
                                    </span>
                                </div>
                            @else
                                <div class="px-3 py-1 rounded-full text-xs bg-amber-100 text-amber-800">
                                    Unverified
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Registration Number</p>
                                <p class="font-medium">{{ $this->business->registration_number }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium">{{ $this->business->email }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium">{{ $this->business->phone }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Address</p>
                                <p class="font-medium">{{ $this->business->address }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Verification Status</p>
                                <div class="flex items-center mt-1">
                                    @if($allDocumentsVerified)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="mr-1 h-3 w-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            All documents verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            @if($this->business->documents()->count() === 0)
                                                No documents uploaded
                                            @else
                                                {{ $this->business->documents()->whereNull('verified_at')->count() }} document(s) need verification
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </x-filament::section>

                </div>

                <!-- Financial Summary Cards -->
                <div class="md:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Total Amount Owing</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-red-600">
                                @php
                                    $totalOwed = \DB::table('debtors')
                                        ->join('business_debtor', 'debtors.id', '=', 'business_debtor.debtor_id')
                                        ->where('debtors.kra_pin', $this->business->registration_number)
                                        ->whereNull('debtors.deleted_at')
                                        ->sum('business_debtor.amount_owed');
                                @endphp
                                KES {{ number_format($totalOwed, 2) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Across {{ $this->business->debtorsToOthers()->count() }} businesses
                            </p>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Total Amount Owed</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-green-600">
                                @php
                                    $totalOwing = $this->business->debtors()->sum('business_debtor.amount_owed');
                                @endphp
                                KES {{ number_format($totalOwing, 2) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                From {{ $this->business->debtors()->count() }} debtors
                            </p>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Disputed Amount</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-amber-600">
                                @php
                                    $disputedAmount = $this->business->debtors()
                                        ->where('status', 'disputed')
                                        ->sum('business_debtor.amount_owed');
                                @endphp
                                KES {{ number_format($disputedAmount, 2) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $this->business->debtors()->where('status', 'disputed')->count() }} active disputes
                            </p>
                        </div>
                    </x-filament::section>

                    <!-- Enhanced Credit Score Section -->
                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Credit Score</h3>
                        <div class="mt-2">
                            @if($this->business->hasCreditScore())
                                <p class="text-3xl font-bold text-{{ $this->business->getRiskColor() }}-600">
                                    {{ $this->business->getCreditScore() }}
                                </p>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $this->business->getRiskColor() }}-100 text-{{ $this->business->getRiskColor() }}-800">
                                        {{ $this->business->getRiskDescription() }} Risk
                                    </span>
                                </div>
                            @else
                                <p class="text-3xl font-bold text-gray-600">
                                    N/A
                                </p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Not enough data to calculate
                                </p>
                            @endif
                        </div>
                    </x-filament::section>
                </div>
            </div>

            <!-- Credit Score Detailed Section -->
            @if($this->business->hasCreditScore())
                <div class="mt-4">
                    <x-filament::section>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold">Credit Risk Assessment</h2>
                                <span class="text-xs text-gray-500">
                                    Last updated: {{ \Carbon\Carbon::parse($this->business->getCreditScoreDetail('Timestamp', now()))->format('M d, Y H:i') }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Main Score -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500 mb-1">Composite Score</p>
                                    <div class="flex items-end">
                                        <span class="text-4xl font-bold text-{{ $this->business->getRiskColor() }}-600">
                                            {{ $this->business->getCreditScore() }}
                                        </span>
                                        <span class="text-sm text-gray-500 ml-2 mb-1">/100</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-{{ $this->business->getRiskColor() }}-600 h-2.5 rounded-full" style="width: {{ $this->business->getCreditScoreDetail('Composite Score', 0) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $this->business->getRiskColor() }}-100 text-{{ $this->business->getRiskColor() }}-800">
                                            {{ $this->business->getRiskDescription() }} Risk (Class {{ $this->business->getRiskClass() }})
                                        </span>
                                    </div>
                                </div>

                                <!-- Risk Factors -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500 mb-2">Component Scores</p>

                                    <div class="space-y-3">
                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-xs font-medium text-gray-700">Payment Probability (PPS)</span>
                                                <span class="text-xs font-medium">{{ number_format($this->business->getCreditScoreDetail('PPS', 0), 1) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $this->business->getCreditScoreDetail('PPS', 0) }}%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">60% of composite score</p>
                                        </div>

                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-xs font-medium text-gray-700">Exposure Score</span>
                                                <span class="text-xs font-medium">{{ number_format($this->business->getCreditScoreDetail('Exposure Score', 0), 1) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $this->business->getCreditScoreDetail('Exposure Score', 0) }}%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">20% of composite score</p>
                                        </div>

                                        <div>
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-xs font-medium text-gray-700">Viability Score (V)</span>
                                                <span class="text-xs font-medium">{{ number_format($this->business->getCreditScoreDetail('V', 0), 1) }}</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $this->business->getCreditScoreDetail('V', 0) }}%"></div>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">20% of composite score</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Details -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm text-gray-500 mb-2">Risk Assessment Details</p>

                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-xs text-gray-500">Risk Factors</p>
                                            <p class="font-medium text-sm">{{ $this->business->getCreditScoreDetail('Reasons for score', 'Not available') }}</p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Probability of Default</p>
                                            <p class="font-medium text-sm">
                                                {{ number_format($this->business->getCreditScoreDetail('Normalized PD', 0) * 100, 1) }}%
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs text-gray-500">Total Amount Owed</p>
                                            <p class="font-medium text-sm">
                                                KES {{ number_format($this->business->getCreditScoreDetail('Total Amount Owed', 0), 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2 text-sm text-gray-500">
                                <p>This credit score is based on your payment history and financial behavior across the Afrimark network.</p>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            @endif

            <!-- Listings Tabs -->
            <div class="mt-6">
                <div x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'businesses-listed-you' }">
                    <!-- Tabs header -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                @click="activeTab = 'businesses-listed-you'; window.location.hash = 'businesses-listed-you'"
                                :class="{ 'border-primary-500 text-primary-600': activeTab === 'businesses-listed-you', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'businesses-listed-you' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Businesses That Listed You
                            </button>

                            <button
                                @click="activeTab = 'your-debtors'; window.location.hash = 'your-debtors'"
                                :class="{ 'border-primary-500 text-primary-600': activeTab === 'your-debtors', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'your-debtors' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Your Debtors
                            </button>
                        </nav>
                    </div>

                    <!-- Tab content -->
                    <div class="mt-6">
                        <!-- Businesses That Listed You Tab -->
                        <!-- Replace the Businesses That Listed You Tab content in your business-profile.blade.php -->
                        <div x-show="activeTab === 'businesses-listed-you'" x-transition>
                            <x-filament::section>
                                @php
                                    // Direct SQL query to get all the data needed with a strong filter on KRA PIN
                                    // Add a HAVING clause to filter out entries with zero invoices
                                    $businessesThatListedYou = DB::select("
                                        SELECT
                                            b.id,
                                            b.name as business_name,
                                            d.id as debtor_id,
                                            d.kra_pin as debtor_kra_pin,
                                            COALESCE((
                                                SELECT SUM(i.due_amount)
                                                FROM invoices i
                                                WHERE i.business_id = b.id
                                                AND i.debtor_id = d.id
                                            ), 0) as total_due_amount,
                                            (
                                                SELECT i.invoice_date
                                                FROM invoices i
                                                WHERE i.business_id = b.id
                                                AND i.debtor_id = d.id
                                                ORDER BY i.invoice_date DESC
                                                LIMIT 1
                                            ) as latest_invoice_date,
                                            (
                                                SELECT COUNT(i.id)
                                                FROM invoices i
                                                WHERE i.business_id = b.id
                                                AND i.debtor_id = d.id
                                            ) as invoice_count
                                        FROM businesses b
                                        JOIN business_debtor bd ON b.id = bd.business_id
                                        JOIN debtors d ON bd.debtor_id = d.id
                                        WHERE d.kra_pin = ? AND d.status = 'active' AND d.deleted_at IS NULL
                                        GROUP BY b.id, b.name, d.id, d.kra_pin
                                        HAVING invoice_count > 0
                                    ", [$this->business->registration_number]);
                                @endphp

                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-sm">Businesses that have listed you</span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700">
                    {{ count($businessesThatListedYou) }}
                </span>
                                    </div>

                                    <div class="flex items-center space-x-2">
                <span class="text-xs text-gray-500">
                    Your KRA PIN: <span class="font-medium">{{ $this->business->registration_number }}</span>
                </span>
                                    </div>
                                </div>

                                <div class="border border-gray-300 rounded-xl overflow-hidden">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left rtl:text-right divide-y divide-gray-200">
                                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                            <tr class="border-b">
                                                <th scope="col" class="px-6 py-3 font-medium">Business Name</th>
                                                <th scope="col" class="px-6 py-3 font-medium">Total Amount Claimed</th>
                                                <th scope="col" class="px-6 py-3 font-medium">Latest Invoice</th>
                                                <th scope="col" class="px-6 py-3 font-medium">Invoice Count</th>
                                                <th scope="col" class="px-6 py-3 font-medium">Your KRA PIN</th>
                                                <th scope="col" class="px-6 py-3 font-medium">Credit Score</th>
                                                <th scope="col" class="px-6 py-3 font-medium text-right">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 bg-white">
                                            @forelse($businessesThatListedYou as $item)
                                                @php
                                                    $businessItem = App\Models\Business::find($item->id);
                                                    $creditScore = $businessItem && $businessItem->hasCreditScore() ? $businessItem->getCreditScore() : 'N/A';
                                                    $riskColor = $businessItem && $businessItem->hasCreditScore() ? $businessItem->getRiskColor() : 'gray';

                                                    // Define badge color classes
                                                    $colorClasses = match($riskColor) {
                                                        'green' => 'bg-success-100 text-success-700',
                                                        'yellow' => 'bg-warning-100 text-warning-700',
                                                        'red' => 'bg-danger-100 text-danger-700',
                                                        default => 'bg-gray-100 text-gray-700',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-4 font-medium text-gray-900">
                                                        {{ $item->business_name }}
                                                    </td>
                                                    <td class="px-6 py-4 font-medium">
                                                        KES {{ number_format($item->total_due_amount, 2) }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        {{ $item->latest_invoice_date ? date('M j, Y', strtotime($item->latest_invoice_date)) : 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center min-w-6 px-2 py-0.5 text-sm font-medium rounded-full bg-gray-100 text-gray-700">
                                        {{ $item->invoice_count }}
                                    </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs bg-primary-50 text-primary-700 rounded-md">
                                        {{ $item->debtor_kra_pin }}
                                    </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClasses }}">
                                        {{ $creditScore }}
                                    </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-right space-x-1">
                                                        <a href="{{ route('filament.client.pages.invoices-listing-you', ['business_id' => $item->id]) }}"
                                                           class="inline-flex items-center justify-center font-medium rounded-lg text-xs px-3 py-1 space-x-1 bg-primary-500 text-white hover:bg-primary-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                                            </svg>
                                                            <span>View Invoices</span>
                                                        </a>

                                                        <a href="{{ route('filament.client.pages.invoices-listing-you', ['business_id' => $item->id]) }}"
                                                           class="inline-flex items-center justify-center font-medium rounded-lg text-xs px-3 py-1 space-x-1 bg-danger-500 text-white hover:bg-danger-600 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500">
                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                            </svg>
                                                            <span>Dispute</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                                        <div class="flex flex-col items-center justify-center space-y-3">
                                                            <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            <span class="text-sm font-medium">No businesses have listed you as a debtor</span>
                                                            <span class="text-xs">Your KRA PIN hasn't been used by other businesses</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="pt-4 text-xs text-gray-500 italic">
                                    This table shows businesses that have listed your KRA PIN ({{ $this->business->registration_number }}) as a debtor through their invoices.
                                </div>
                            </x-filament::section>
                        </div>

                        <!-- Your Debtors Tab -->
                        <div x-show="activeTab === 'your-debtors'" x-transition>
                            <x-filament::section>
                                {{ $this->table }}
                            </x-filament::section>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(!$this->business)
            <x-filament::section>
                <div class="text-center py-4">
                    <h2 class="text-lg font-medium text-gray-900">Business Profile Not Found</h2>
                    <p class="mt-1 text-sm text-gray-500">Please complete your business registration first.</p>
                    <div class="mt-4">
                        <x-filament::button
                            tag="a"
                            href="{{ route('filament.client.auth.business-information') }}"
                            color="primary"
                        >
                            Complete Registration
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
