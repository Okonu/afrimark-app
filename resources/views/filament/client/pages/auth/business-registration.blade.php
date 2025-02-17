<x-filament-panels::page.simple>
    @pushOnce('styles')
        <style>
            /* Core layout adjustments */
            .fi-simple-main,
            .fi-wi-step,
            .fi-form-component,
            .fi-wizard-step {
                width: 100% !important;
                min-width: 100% !important;
                max-width: 100% !important;
            }

            /* Ensure content spans full width */
            .fi-wizard-step-content {
                grid-column: 1 / -1;
            }

            /* Adjust form spacing */
            .fi-fo-field-wrp {
                max-width: none !important;
            }

            /* Container adjustments */
            .fi-simple-page {
                --container-width: 100% !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        </style>
    @endPushOnce

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <div class="mt-6 flex items-center justify-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Already registered?</span>
            <a href="{{ route('filament.client.auth.login') }}" class="text-sm text-primary-600 hover:text-primary-500 font-medium ml-1">
                Sign in
            </a>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page.simple>
