<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Afrimark') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
<div class="max-w-md w-full space-y-8">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900">Afrimark</h1>
        <h2 class="mt-2 text-xl font-semibold text-gray-700">Email Verification</h2>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Email Verification Required</h3>

        <p class="text-gray-600 mb-4">
            We've sent a verification email to <span class="font-medium">{{ $business->email }}</span>.
        </p>

        <p class="text-gray-600 mb-6">
            Please check your inbox and click on the verification link to continue. If you don't see
            the email, check your spam folder or click the button below to resend the verification email.
        </p>

        <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
            <button wire:click="resendVerificationEmail" type="button" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                Resend Verification Email
            </button>

            <button wire:click="skipVerification" type="button" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                Skip for Now
            </button>
        </div>

        <div class="mt-6 text-sm text-gray-500">
            <p>
                <strong>Note:</strong> Verifying your business email is important for security and to receive notifications about your account. You can continue the registration process now, but some features will be limited until your email is verified.
            </p>
        </div>
    </div>
</div>

@livewireScripts
</body>
</html>
