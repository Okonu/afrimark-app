<!-- components/business-search/header.blade.php -->
<x-filament::section>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900">Business Credit Check</h2>
            <p class="mt-1 text-sm text-gray-500">Search for a business by name or KRA PIN to view its credit report</p>
        </div>
        <div class="flex items-center space-x-2">
            <x-filament::badge color="primary">
                <span class="flex items-center gap-1">
                    <x-heroicon-s-shield-check class="h-3.5 w-3.5" />
                    Credit API Connected
                </span>
            </x-filament::badge>
        </div>
    </div>
</x-filament::section>
