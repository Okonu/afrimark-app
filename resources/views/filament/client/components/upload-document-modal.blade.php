{{--
    Upload Document Modal Component
    resources/views/filament/client/components/upload-document-modal.blade.php
--}}

<x-filament::modal
    wire:model.defer="isUploadModalOpen"
    width="md"
    id="upload-document-modal"
>
    <x-slot name="heading">
        Upload New Document
    </x-slot>

    <x-slot name="description">
        Complete the form below to upload a new document.
    </x-slot>

    <div class="space-y-4">
        @if($isUploadModalOpen)
            {{ $this->form }}
        @endif
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-4">
            <x-filament::button wire:click="closeUploadModal" color="gray">
                Cancel
            </x-filament::button>

            <x-filament::button wire:click="submit" color="primary">
                Upload Document
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>
