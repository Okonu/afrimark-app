<x-filament::page>
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="mt-4 flex justify-end gap-3">
            @foreach ($this->getHeaderActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament::page>
