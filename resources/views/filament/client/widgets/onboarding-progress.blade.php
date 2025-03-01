<x-filament::section>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold tracking-tight">Onboarding Progress</h2>

            @if($progress && $progress['percentage'] < 100)
                <x-filament::button
                    tag="a"
                    href="{{ $nextStepUrl }}"
                    color="primary"
                >
                    Complete Next Step
                </x-filament::button>
            @endif
        </div>

        @if(!$progress)
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-600">
                    Please complete your business registration to track your onboarding progress.
                </p>
            </div>
        @else
            <div class="space-y-4">
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div
                        class="bg-primary-600 h-4 rounded-full transition-all duration-500 ease-in-out"
                        style="width: {{ $progress['percentage'] }}%"
                    ></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
</x-filament::section>
