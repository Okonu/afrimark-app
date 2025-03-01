<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Afrimark Business Portal</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-gray-50">
<!-- Main Header -->
<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-600" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
                <span class="ml-3 text-xl font-semibold text-gray-900">Afrimark</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('filament.client.auth.login') }}"
                   class="text-gray-500 hover:text-gray-700">
                    Login
                </a>
                <a href="{{ route('filament.client.auth.register') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-amber-600 hover:bg-amber-700">
                    Register Now
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Hero Section -->
<main class="pt-16 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Streamline Your</span>
                <span class="block text-amber-600">Business Credit Management</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Register your business and gain access to our comprehensive credit management platform. Track debtors, manage disputes, and build your business credit profile efficiently.
            </p>
            <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                <div class="rounded-md shadow">
                    <a href="{{ route('filament.client.auth.register') }}"
                       class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 md:py-4 md:text-lg md:px-10">
                        Get Started
                    </a>
                </div>
                <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                    <a href="{{ route('filament.client.auth.login') }}"
                       class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-amber-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                        Sign In
                    </a>
                </div>
            </div>
        </div>

        <!-- Social Login Section -->
        <div class="mt-12 text-center">
            <p class="text-gray-600 text-lg">Quick registration with:</p>
            <div class="mt-4 flex justify-center space-x-6">
                <a href="{{ route('social.login', ['provider' => 'google']) }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                    </svg>
                    Google
                </a>
                <a href="{{ route('social.login', ['provider' => 'linkedin']) }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                    LinkedIn
                </a>
                <a href="{{ route('social.login', ['provider' => 'microsoft']) }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/>
                    </svg>
                    Microsoft
                </a>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="mt-24">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Credit Management Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Business Credit Management</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Track your debtors, manage payments, and build your business credit profile in one place.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Dispute Resolution Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Dispute Resolution</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Efficiently manage and resolve payment disputes with a transparent system.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Business Search Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Business Search</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Search for businesses and view their credit profiles before entering into new agreements.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registration Steps -->
        <div class="mt-24">
            <h2 class="text-3xl font-extrabold text-gray-900 text-center mb-12">Simple Registration Process</h2>
            <div class="max-w-5xl mx-auto">
                <div class="relative">
                    <!-- Step connector -->
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>

                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        <!-- Step 1 -->
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-600 text-white font-bold">
                                1
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Sign Up</h3>
                                <p class="mt-1 text-sm text-gray-500">Simple account creation</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-600 text-white font-bold">
                                2
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Business Details</h3>
                                <p class="mt-1 text-sm text-gray-500">Add your business information</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-600 text-white font-bold">
                                3
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Verification</h3>
                                <p class="mt-1 text-sm text-gray-500">Verify your email address</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-amber-600 text-white font-bold">
                                4
                            </div>
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Get Started</h3>
                                <p class="mt-1 text-sm text-gray-500">Access your dashboard</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- CTA Section -->
<div class="bg-amber-50">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:py-16 lg:px-8 lg:flex lg:items-center lg:justify-between">
        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            <span class="block">Ready to improve your credit management?</span>
            <span class="block text-amber-600">Start your journey today.</span>
        </h2>
        <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
            <div class="inline-flex rounded-md shadow">
                <a href="{{ route('filament.client.auth.register') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700">
                    Register Now
                </a>
            </div>
            <div class="ml-3 inline-flex rounded-md shadow">
                <a href="{{ route('filament.client.auth.login') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-amber-600 bg-white hover:bg-amber-50">
                    Log In
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-white">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
        <div class="flex justify-center space-x-6 md:order-2">
            <span class="text-gray-400">&copy; {{ date('Y') }} Afrimark. All rights reserved.</span>
        </div>
    </div>
</footer>
</body>
</html>
