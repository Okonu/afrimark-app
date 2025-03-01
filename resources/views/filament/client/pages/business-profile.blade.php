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
                        <h3 class="text-base font-medium text-gray-900">Total Amount Owed</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-red-600">
                                KES {{ number_format($this->business->debtorsToOthers()->sum('amount_owed'), 2) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Across {{ $this->business->debtorsToOthers()->count() }} businesses
                            </p>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Total Amount Owing</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-green-600">
                                KES {{ number_format($this->business->debtors()->sum('amount_owed'), 2) }}
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
                                KES {{ number_format($this->business->debtors()->where('status', 'disputed')->sum('amount_owed'), 2) }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $this->business->debtors()->where('status', 'disputed')->count() }} active disputes
                            </p>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <h3 class="text-base font-medium text-gray-900">Credit Score</h3>
                        <div class="mt-2">
                            <p class="text-3xl font-bold text-blue-600">
                                {{ $this->business->credit_score ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Based on payment history
                            </p>
                        </div>
                    </x-filament::section>
                </div>
            </div>

            <!-- Listings Tabs -->
            <div class="mt-6">
                <div x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'listed-by-others' }">
                    <!-- Tabs header -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                @click="activeTab = 'listed-by-others'; window.location.hash = 'listed-by-others'"
                                :class="{ 'border-primary-500 text-primary-600': activeTab === 'listed-by-others', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'listed-by-others' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                Listed By Others
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
                        <!-- Listed By Others Tab -->
                        <div x-show="activeTab === 'listed-by-others'" x-transition>
                            <x-filament::section>
                                {{ $this->getListedByOthersTable() }}
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
