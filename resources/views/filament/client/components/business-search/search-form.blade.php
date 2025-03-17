<!-- components/business-search/search-form.blade.php -->
<x-filament::section>
    <form wire:submit="search" class="mt-4">
        <div class="grid gap-4">
            {{ $this->form }}
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <x-filament::button type="submit" size="lg" icon="heroicon-m-magnifying-glass" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="search">Search Business</span>
                <span wire:loading wire:target="search">Searching...</span>
            </x-filament::button>

            @if($searchPerformed)
                <x-filament::button type="button" color="gray" size="lg" icon="heroicon-m-x-mark" wire:click="clearSearch">
                    Clear Results
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament::section>
