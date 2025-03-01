<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Email Verification Required</h2>

            <div class="mb-6">
                <p class="text-gray-600 mb-4">
                    We've sent a verification email to <span class="font-medium">{{ $business->email }}</span>.
                </p>
                <p class="text-gray-600 mb-4">
                    Please check your inbox and click on the verification link to continue. If you don't see the email,
                    check your spam folder or click the button below to resend the verification email.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <x-filament::button wire:click="resendVerificationEmail" color="primary">
                    Resend Verification Email
                </x-filament::button>

                <x-filament::button wire:click="skipVerification" color="gray">
                    Skip for Now
                </x-filament::button>
            </div>

            <div class="mt-6 text-sm text-gray-500">
                <p>
                    <strong>Note:</strong> Verifying your business email is important for security and to receive
                    notifications about your account. You can continue the registration process now, but some features
                    will be limited until your email is verified.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
