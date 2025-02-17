<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Welcome to Business Portal</title>

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
                <span class="ml-3 text-xl font-semibold text-gray-900">Business Portal</span>
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
                <span class="block text-amber-600">Business Operations</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Register your business and get access to our comprehensive business management platform. Manage documents, track operations, and grow your business efficiently.
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

        <!-- Features Grid -->
        <div class="mt-24">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Easy Registration Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Easy Registration</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Simple step-by-step registration process with guided document submission.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Secure Storage Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Secure Storage</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Safe and secure document storage with controlled access.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Document Management Feature -->
                <div class="pt-6">
                    <div class="flow-root bg-white rounded-lg px-6 pb-8">
                        <div class="-mt-6">
                            <div>
                                    <span class="inline-flex items-center justify-center p-3 bg-amber-500 rounded-md shadow-lg">
                                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </span>
                            </div>
                            <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Document Management</h3>
                            <p class="mt-5 text-base text-gray-500">
                                Efficient document tracking and management system.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-white">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
        <div class="flex justify-center space-x-6 md:order-2">
            <span class="text-gray-400">&copy; {{ date('Y') }} Your Company. All rights reserved.</span>
        </div>
    </div>
</footer>
</body>
</html>
