<div>
    @php
        $hasLogo = true;
    @endphp

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
        <div class="w-full sm:max-w-md mt-6 p-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    Login to your Account
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Please sign in to access your business dashboard.
                </p>
            </div>

            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="authenticate">
                {{ $this->form }}

                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <x-filament::link href="{{ route('filament.client.auth.register') }}">
                            Register new business
                        </x-filament::link>
                    </div>

                    <div class="flex items-center gap-x-3">
                        @if ($this->getResetPasswordUrl())
                            <x-filament::link href="{{ $this->getResetPasswordUrl() }}">
                                Forgot password?
                            </x-filament::link>
                        @endif

                        <x-filament::button type="submit">
                            Login
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
