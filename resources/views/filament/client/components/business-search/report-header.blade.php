<!-- components/business-search/report-header.blade.php -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200">
    <div>
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold tracking-tight text-gray-900">Business Credit Report</h2>
            @if(isset($businessReport['is_registered']) && $businessReport['is_registered'])
                <x-filament::badge color="success">Registered</x-filament::badge>
            @else
                <x-filament::badge color="warning">Unregistered</x-filament::badge>
            @endif
        </div>
        <p class="mt-1 text-sm text-gray-500">
            <span class="font-medium">{{ $businessReport['name'] ?? 'Unknown Business' }}</span>
            @if(isset($businessReport['registration_number']) || isset($businessReport['kra_pin']))
                <span class="mx-1">•</span>
                <span>
                    @if(isset($businessReport['is_registered']) && $businessReport['is_registered'] && isset($businessReport['registration_number']))
                        {{ $businessReport['registration_number'] }}
                    @elseif(isset($businessReport['kra_pin']))
                        {{ $businessReport['kra_pin'] }}
                    @endif
                </span>
            @endif
        </p>
    </div>
    <x-filament::button
        color="gray"
        size="sm"
        icon="heroicon-m-arrow-left"
        wire:click="$set('businessReport', null)"
    >
        Back to Results
    </x-filament::button>
</div>
