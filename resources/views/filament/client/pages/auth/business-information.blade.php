<x-filament-panels::page.simple>
    <x-slot name="heading">
        Business Information
    </x-slot>

    <p class="text-gray-600 mb-6">
        Please provide details about your business. This information will be used to create your business profile.
    </p>

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" class="w-full">
                Save & Continue
            </x-filament::button>
        </div>
    </x-filament-panels::form>

    <div class="mt-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-gray-700">
                    You'll need to verify your business email in the next step before proceeding.
                </p>
            </div>
        </div>
    </div>

    <!-- Progress indicators -->
    <div class="relative mt-8">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-between">
            <div>
                <span class="bg-white px-2 text-gray-500 text-sm">
                    Step 2 of 4
                </span>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
