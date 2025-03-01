<x-filament-panels::page.simple>
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                Business Verification Documents
            </h2>

            <p class="text-gray-600 mb-6">
                Upload the required documents to verify your business. These documents help establish the legitimacy
                of your business on our platform. You can skip this step and complete it later from your dashboard.
            </p>

            <x-filament-panels::form wire:submit="submit">
                {{ $this->form }}

                <div class="mt-6 flex items-center justify-between">
                    <x-filament::button
                        wire:click="skipDocumentUpload"
                        color="gray"
                        type="button"
                    >
                        Skip for Now
                    </x-filament::button>

                    <x-filament::button type="submit">
                        Upload Documents
                    </x-filament::button>
                </div>
            </x-filament-panels::form>

            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-gray-700">Document Requirements</h3>
                        <div class="mt-2 text-sm text-gray-600">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>All documents must be in PDF format</li>
                                <li>Maximum file size: 10MB per document</li>
                                <li>Documents must be clear and legible</li>
                                <li>Documents must be current and valid</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress indicators -->
        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-between">
                <div>
                    <span class="bg-white px-2 text-gray-500 text-sm">
                        Step 4 of 4
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
